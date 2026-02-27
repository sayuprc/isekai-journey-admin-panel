/**
 * API レスポンスから data を取り出し、存在しなければエラーを投げる。
 *
 * @param result - openapi-fetch の返り値 ({ data, error })
 * @param context - エラー時のログメッセージ（省略可）
 */
export const unwrapOrThrow = <T>(
  result: { data?: T; error?: { message?: string } },
  context?: string,
): T => {
  if (result.data !== undefined) {
    return result.data;
  }

  if (context) {
    console.error(context);
  }

  if (result.error?.message) {
    console.error(result.error.message);
  }

  throw new Error(context ?? 'API request failed');
};
