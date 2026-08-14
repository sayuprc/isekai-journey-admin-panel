///|
/// Discord へ送る正規化済み通知
pub struct Notification {
  action : String
  status : String
  /// Discord webhook の content (無い/空なら payload に載せない)
  content : String?
  /// Discord embed 配列 (無い/空なら payload に載せない)
  /// content と embeds のどちらか一方は必須
  embeds : Json?
} derive(Eq, Debug)

///|
/// 1 メッセージ処理の結果
pub(all) enum Outcome {
  /// Pub/Sub に ack してよい (配達成功 / 意図的スキップ / 恒久失敗)
  Ack
  /// 一時失敗。Pub/Sub に再送させる
  Retry(String)
} derive(Eq, Debug)

///|
pub suberror ParseError {
  InvalidJson(String)
  InvalidBase64(String)
  InvalidPayload(String)
} derive(Eq, Debug)

///|
pub fn ParseError::to_string(self : ParseError) -> String {
  match self {
    InvalidJson(msg) => "invalid json: \{msg}"
    InvalidBase64(msg) => "invalid base64: \{msg}"
    InvalidPayload(msg) => "invalid payload: \{msg}"
  }
}
