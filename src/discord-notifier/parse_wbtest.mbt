///|
fn encode_pubsub(inner : String) -> String {
  let data = @base64.encode(@utf8.encode(inner))
  "{\"message\":{\"data\":\"\{data}\"}}"
}

///|
test "parse app notification raw" {
  let body = "{\"action\":\"media.youtube_import\",\"status\":\"failed\",\"embeds\":[{\"title\":\"boom\",\"fields\":[{\"name\":\"executed_at\",\"value\":\"2026-01-01T00:00:00Z\"}]}]}"
  let notification = parse_request_body(
    body,
    cloud_build_trigger_name="dev-isekai-ci",
  )
  guard notification is Some(n) else { fail("expected notification") }
  assert_eq(n.action, "media.youtube_import")
  assert_eq(n.status, "failed")
  guard n.embeds is Array(items) else { fail("expected embeds") }
  assert_eq(items.length(), 1)
}

///|
test "parse app notification via pubsub envelope" {
  let inner = "{\"action\":\"admin.invite\",\"status\":\"succeeded\",\"embeds\":[{\"title\":\"ok\"}]}"
  let body = encode_pubsub(inner)
  let notification = parse_request_body(
    body,
    cloud_build_trigger_name="dev-isekai-ci",
  )
  guard notification is Some(n) else { fail("expected notification") }
  assert_eq(n.action, "admin.invite")
  assert_eq(n.status, "succeeded")
}

///|
test "parse app notification requires action" {
  let err = @test.expect_error(() => {
    parse_request_body(
      "{\"status\":\"failed\",\"embeds\":[{\"title\":\"boom\"}]}",
      cloud_build_trigger_name="dev-isekai-ci",
    )
    |> ignore
  })
  guard err is InvalidPayload(_) else { fail("expected InvalidPayload") }
}

///|
test "parse app notification rejects empty embeds" {
  let err = @test.expect_error(() => {
    parse_request_body(
      "{\"action\":\"admin.invite\",\"status\":\"succeeded\",\"embeds\":[]}",
      cloud_build_trigger_name="dev-isekai-ci",
    )
    |> ignore
  })
  guard err is InvalidPayload(_) else { fail("expected InvalidPayload") }
}

///|
test "parse cloud build working" {
  let inner = "{\"id\":\"build-1\",\"status\":\"WORKING\",\"logUrl\":\"https://example.com/log\",\"substitutions\":{\"TRIGGER_NAME\":\"dev-isekai-ci\"}}"
  let notification = parse_request_body(
    encode_pubsub(inner),
    cloud_build_trigger_name="dev-isekai-ci",
  )
  guard notification is Some(n) else { fail("expected notification") }
  assert_eq(n.action, "cloud_build")
  assert_eq(n.status, "started")
  guard n.embeds
    is [
      {
        "title": String(title),
        "url": String(url),
        "fields": Array(fields),
        ..
      },
    ] else {
    fail("expected embed")
  }
  assert_eq(title, "Cloud Build が開始しました")
  assert_eq(url, "https://example.com/log")
  assert_eq(fields.length(), 2)
}

///|
test "parse cloud build without log url" {
  let inner = "{\"id\":\"build-2\",\"status\":\"SUCCESS\",\"substitutions\":{\"TRIGGER_NAME\":\"dev-isekai-ci\"}}"
  let notification = parse_request_body(
    encode_pubsub(inner),
    cloud_build_trigger_name="dev-isekai-ci",
  )
  guard notification is Some(n) else { fail("expected notification") }
  assert_eq(n.status, "succeeded")
  guard n.embeds is Array(items) else { fail("expected embeds") }
  assert_eq(items.length(), 1)
  guard items[0] is Object(map) else { fail("expected object") }
  guard map.get("title") is Some(String(title)) else { fail("expected title") }
  assert_eq(title, "Cloud Build が成功しました")
  assert_eq(map.get("url"), None)
  guard map.get("fields") is Some(Array(fields)) else {
    fail("expected fields")
  }
  assert_eq(fields.length(), 2)
}

///|
test "parse cloud build ignores other trigger" {
  let inner = "{\"id\":\"build-1\",\"status\":\"SUCCESS\",\"substitutions\":{\"TRIGGER_NAME\":\"other\"}}"
  let notification = parse_request_body(
    encode_pubsub(inner),
    cloud_build_trigger_name="dev-isekai-ci",
  )
  assert_eq(notification, None)
}

///|
test "parse cloud build failed statuses" {
  let statuses = ["FAILURE", "INTERNAL_ERROR", "TIMEOUT", "CANCELLED"]
  for status in statuses {
    let inner = "{\"id\":\"b\",\"status\":\"\{status}\",\"substitutions\":{\"TRIGGER_NAME\":\"dev-isekai-ci\"}}"
    let notification = parse_request_body(
      encode_pubsub(inner),
      cloud_build_trigger_name="dev-isekai-ci",
    )
    guard notification is Some(n) else { fail("expected \{status}") }
    assert_eq(n.action, "cloud_build")
    assert_eq(n.status, "failed")
  }
}

///|
test "resolve channel from action routing" {
  let config : Config = {
    default_channel: "app",
    action_routing: { "cloud_build": "build", "ops.alert": "ops" },
    cloud_build_trigger_name: "dev-isekai-ci",
    port: 8080,
  }
  assert_eq(config.resolve_channel("cloud_build"), "build")
  assert_eq(config.resolve_channel("ops.alert"), "ops")
  assert_eq(config.resolve_channel("media.youtube_import"), "app")
  assert_eq(config.resolve_channel(""), "app")
  assert_eq(webhook_env_name("build"), "DISCORD_WEBHOOK_BUILD")
  assert_eq(webhook_env_name("app"), "DISCORD_WEBHOOK_APP")
}

///|
test "dedupe started builds" {
  let deduper = StartedBuildDeduper::new()
  assert_true(deduper.should_notify_started("build-1"))
  assert_false(deduper.should_notify_started("build-1"))
  assert_true(deduper.should_notify_started("build-2"))
}

///|
test "discord payload fills status color when missing" {
  let payload = build_discord_payload({
    action: "media.youtube_import",
    status: "failed",
    embeds: [
      {
        "title": "取り込みでエラーが発生しました",
        "fields": [
          {
            "name": "executed_at",
            "value": "2026-01-01T00:00:00Z",
            "inline": true,
          },
        ],
      },
    ],
  })
  guard payload
    is {
      "embeds": [
        {
          "title": String(title),
          "color": Number(color, ..),
          "fields": fields,
          ..
        },
      ],
      ..
    } else {
    fail("expected colored embed")
  }
  assert_eq(title, "取り込みでエラーが発生しました")
  assert_eq(color.to_int(), 0xED4245)
  guard fields is Array(items) else { fail("expected fields") }
  assert_eq(items.length(), 1)
}

///|
test "discord payload keeps explicit embed color" {
  let payload = build_discord_payload({
    action: "media.youtube_import",
    status: "failed",
    embeds: [{ "title": "custom", "color": 1 }],
  })
  guard payload is { "embeds": [{ "color": Number(color, ..), .. }], .. } else {
    fail("expected embed")
  }
  assert_eq(color.to_int(), 1)
}

///|
test "discord payload colors by status" {
  let succeeded = build_discord_payload({
    action: "cloud_build",
    status: "succeeded",
    embeds: [{ "title": "ok" }],
  })
  guard succeeded is { "embeds": [{ "color": Number(color, ..), .. }], .. } else {
    fail("expected embed")
  }
  assert_eq(color.to_int(), 0x57F287)

  let started = build_discord_payload({
    action: "cloud_build",
    status: "started",
    embeds: [{ "title": "working" }],
  })
  guard started
    is { "embeds": [{ "color": Number(started_color, ..), .. }], .. } else {
    fail("expected embed")
  }
  assert_eq(started_color.to_int(), 0x3498DB)
}
