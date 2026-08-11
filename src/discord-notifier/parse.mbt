///|
/// HTTP body (Pub/Sub push envelope または生 JSON) から Notification を取り出す
/// Cloud Build で対象外 / 未対応 status のときは None
pub fn parse_request_body(
  body : String,
  cloud_build_trigger_name~ : String,
) -> Notification? raise ParseError {
  let root = @json.parse(body) catch {
    _ => raise InvalidJson("failed to parse request body")
  }
  let payload = extract_payload_json(root)
  parse_payload_json(payload, cloud_build_trigger_name~)
}

///|
fn extract_payload_json(root : Json) -> Json raise ParseError {
  match root {
    { "message": { "data": String(data), .. }, .. } => {
      let bytes = @base64.decode(data, ignore_whitespace=true) catch {
        _ => raise InvalidBase64("failed to decode message.data")
      }
      let text = @utf8.decode(bytes) catch {
        _ => raise InvalidPayload("message.data is not valid utf8")
      }
      @json.parse(text) catch {
        _ => raise InvalidJson("failed to parse decoded message.data")
      }
    }
    _ => root
  }
}

///|
fn parse_payload_json(
  payload : Json,
  cloud_build_trigger_name~ : String,
) -> Notification? raise ParseError {
  match payload {
    { "action": String(action), "status": String(status), "embeds": embeds, .. } => {
      guard has_embeds(embeds) else {
        raise InvalidPayload("embeds must be a non-empty array")
      }
      Some({ action, status, embeds })
    }
    { "id": String(_), "status": String(status), .. } =>
      parse_cloud_build(payload, status, cloud_build_trigger_name~)
    _ =>
      raise InvalidPayload(
        "expected app notification with action/status/embeds or Cloud Build JSON",
      )
  }
}

///|
fn parse_cloud_build(
  payload : Json,
  status : String,
  cloud_build_trigger_name~ : String,
) -> Notification? {
  let trigger_name = extract_trigger_name(payload)
  guard cloud_build_trigger_name != "" &&
    trigger_name == cloud_build_trigger_name else {
    return None
  }
  let (notify_status, title) = match status {
    "WORKING" => ("started", "Cloud Build が開始しました")
    "SUCCESS" => ("succeeded", "Cloud Build が成功しました")
    "FAILURE" | "INTERNAL_ERROR" | "TIMEOUT" | "CANCELLED" =>
      ("failed", "Cloud Build が失敗しました")
    _ => return None
  }
  let build_id = match payload {
    { "id": String(id), .. } => id
    _ => "unknown"
  }
  let log_url = match payload {
    { "logUrl": String(url), .. } => url
    _ => ""
  }
  let fields : Json = [
    { "name": "build_id", "value": build_id, "inline": true },
    { "name": "trigger", "value": trigger_name, "inline": true },
  ]
  let embed : Json = if log_url == "" {
    { "title": title, "fields": fields }
  } else {
    { "title": title, "url": log_url, "fields": fields }
  }
  Some({ action: "cloud_build", status: notify_status, embeds: [embed] })
}

///|
fn extract_trigger_name(payload : Json) -> String {
  match payload {
    { "substitutions": { "TRIGGER_NAME": String(name), .. }, .. } => name
    _ => ""
  }
}

///|
/// build started の重複抑止用に build id を取り出す
pub fn cloud_build_id(payload_json_text : String) -> String? {
  let root = @json.parse(payload_json_text) catch { _ => return None }
  let payload = extract_payload_json(root) catch { _ => return None }
  match payload {
    { "id": String(id), "status": String(_), .. } => Some(id)
    _ => None
  }
}
