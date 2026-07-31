import { createSignal } from 'solid-js';

type ErrorDetail = {
  field: string;
  message: string;
};

type ErrorResponseBody = {
  code: string;
  message: string;
  details?: ErrorDetail[];
};

type EdenError = {
  status: number;
  value: unknown;
};

const isEdenError = (error: unknown): error is EdenError =>
  typeof error === 'object' && error !== null && 'status' in error && 'value' in error;

const extractErrorBody = (error: unknown): unknown => (isEdenError(error) ? error.value : error);

const isErrorResponse = (body: unknown): body is ErrorResponseBody =>
  typeof body === 'object'
  && body !== null
  && 'code' in body
  && 'message' in body
  && typeof (body as ErrorResponseBody).message === 'string';

export const createFormErrors = () => {
  const [formError, setFormError] = createSignal<string | null>(null);
  const [fieldErrors, setFieldErrors] = createSignal<ErrorDetail[]>([]);

  const getFieldError = (field: string): string | undefined => fieldErrors().find(e => e.field === field)?.message;

  const clearErrors = () => {
    setFormError(null);
    setFieldErrors([]);
  };

  const handleError = (status: number, error: unknown) => {
    clearErrors();

    if (status === 401) {
      window.location.href = '/auth/login';
      return;
    }

    const body = extractErrorBody(error);

    if (!isErrorResponse(body)) {
      setFormError('予期しないエラーが発生しました');
      return;
    }

    if (status === 422 && body.details !== undefined) {
      setFieldErrors(body.details);
      return;
    }

    setFormError(body.message);
  };

  return { formError, setFormError, fieldErrors, getFieldError, clearErrors, handleError };
};
