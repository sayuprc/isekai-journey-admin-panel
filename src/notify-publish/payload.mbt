///|
/// discord-notifier と同じアプリ通知契約を検証する
/// action / status と content または非空 embeds が必須
pub fn validate_notification_json(text : String) -> Unit raise PayloadError {
  let root = @json.parse(text) catch {
    err => raise InvalidJson(err.to_string())
  }
  match root {
    { "action": String(action), "status": String(status), .. } => {
      guard action != "" else {
        raise InvalidPayload("action must not be empty")
      }
      guard status != "" else {
        raise InvalidPayload("status must not be empty")
      }
      let content = extract_content(root)
      let embeds = extract_embeds(root)
      guard content is Some(_) || embeds is Some(_) else {
        raise InvalidPayload("content or non-empty embeds is required")
      }
    }
    _ => raise InvalidPayload("expected notification with action/status")
  }
}

///|
fn extract_content(payload : Json) -> String? {
  match payload {
    { "content": String(content), .. } =>
      if content == "" {
        None
      } else {
        Some(content)
      }
    _ => None
  }
}

///|
fn extract_embeds(payload : Json) -> Json? raise PayloadError {
  match payload {
    { "embeds": embeds, .. } => {
      guard embeds is Array(_) else {
        raise InvalidPayload("embeds must be an array")
      }
      if has_embeds(embeds) {
        Some(embeds)
      } else {
        None
      }
    }
    _ => None
  }
}

///|
fn has_embeds(embeds : Json) -> Bool {
  match embeds {
    Array(items) => items.length() > 0
    _ => false
  }
}

///|
/// Pub/Sub PublishRequest の body を作る
pub fn build_publish_body(payload_text : String) -> Json {
  let data = @base64.encode(@utf8.encode(payload_text))
  { "messages": [{ "data": data }] }
}
