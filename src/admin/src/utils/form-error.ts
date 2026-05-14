import { createSignal } from 'solid-js';

type ValidationErrorDetail = {
  field: string;
  message: string;
};

type ValidationErrorBody = {
  errors: ValidationErrorDetail[];
};

type ErrorResponseBody = {
  message: string;
};

type EdenError = {
  status: number;
  value: unknown;
};

const isEdenError = (error: unknown): error is EdenError =>
  typeof error === 'object' && error !== null && 'status' in error && 'value' in error;

const extractErrorBody = (error: unknown): unknown => (isEdenError(error) ? error.value : error);

const isValidationError = (body: unknown): body is ValidationErrorBody =>
  typeof body === 'object' && body !== null && 'errors' in body && Array.isArray((body as ValidationErrorBody).errors);

const isErrorResponse = (body: unknown): body is ErrorResponseBody =>
  typeof body === 'object' &&
  body !== null &&
  'message' in body &&
  typeof (body as ErrorResponseBody).message === 'string';

export const createFormErrors = () => {
  const [formError, setFormError] = createSignal<string | null>(null);
  const [fieldErrors, setFieldErrors] = createSignal<ValidationErrorDetail[]>([]);

  const getFieldError = (field: string): string | undefined => fieldErrors().find((e) => e.field === field)?.message;

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

    if (status === 422 && isValidationError(body)) {
      setFieldErrors(body.errors);
      return;
    }

    if (isErrorResponse(body)) {
      setFormError(body.message);
      return;
    }

    setFormError('予期しないエラーが発生しました');
  };

  return { formError, setFormError, fieldErrors, getFieldError, clearErrors, handleError };
};
