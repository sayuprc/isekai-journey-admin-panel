import { GOOGLE_CLOUD_PROJECT } from 'astro:env/server';

/**
 * Cloud Logging が解釈する severity。
 *
 * @see https://cloud.google.com/logging/docs/reference/v2/rest/v2/LogEntry#LogSeverity
 */
type Severity = 'DEBUG' | 'INFO' | 'WARNING' | 'ERROR' | 'CRITICAL';

type LogFields = Record<string, unknown>;

/**
 * `X-Cloud-Trace-Context` (形式: `TRACE_ID/SPAN_ID;o=TRACE_TRUE`) を解釈し、
 * Cloud Logging のトレース相関フィールドを組み立てる。
 *
 * プロジェクト ID が無い、またはヘッダーが無い場合は相関なし (空オブジェクト)。
 */
const traceFields = (request: Request | undefined): LogFields => {
  if (request === undefined || !GOOGLE_CLOUD_PROJECT) {
    return {};
  }

  const header = request.headers.get('x-cloud-trace-context');

  if (header === null || header === '') {
    return {};
  }

  const [traceId, rest] = header.split('/', 2);

  if (!traceId) {
    return {};
  }

  const fields: LogFields = {
    'logging.googleapis.com/trace': `projects/${GOOGLE_CLOUD_PROJECT}/traces/${traceId}`,
  };

  const spanId = rest?.split(';', 1)[0];

  if (spanId) {
    fields['logging.googleapis.com/spanId'] = spanId;
  }

  return fields;
};

/**
 * Cloud Run が回収する stdout へ Cloud Logging 互換の構造化 JSON を 1 行で出力する。
 *
 * `request` を渡すと、そのリクエストの trace とログを相関させる。
 */
export const log = (severity: Severity, message: string, fields: LogFields = {}, request?: Request): void => {
  const entry: LogFields = {
    severity,
    message,
    ...traceFields(request),
    ...fields,
  };

  console.log(JSON.stringify(entry));
};

export const logError = (message: string, fields?: LogFields, request?: Request): void =>
  log('ERROR', message, fields, request);
