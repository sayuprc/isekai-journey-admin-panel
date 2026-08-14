///|
/// 通知 JSON の検証エラー
pub suberror PayloadError {
  InvalidJson(String)
  InvalidPayload(String)
} derive(Eq, Debug)

///|
pub fn PayloadError::to_string(self : PayloadError) -> String {
  match self {
    InvalidJson(msg) => "invalid json: \{msg}"
    InvalidPayload(msg) => "invalid payload: \{msg}"
  }
}
