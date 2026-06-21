import "@typespec/http";

using Http;

namespace IsekaiObservatory.Shared;

model Ok<T> {
  @statusCode _: 200;
  @body body: T;
}

model NoContent {
  @statusCode _: 204;
}

model BadRequest {
  @statusCode _: 400;
  @body body: ErrorResponse;
}

model Unauthorized {
  @statusCode _: 401;
}

model Forbidden {
  @statusCode _: 403;
}

model NotFound {
  @statusCode _: 404;
  @body body: ErrorResponse;
}

model Unprocessable {
  @statusCode _: 422;
  @body body: ValidationError;
}

@friendlyName("ValidationError")
model ValidationError {
  errors: ValidationErrorDetail[];
}

@friendlyName("ValidationErrorDetail")
model ValidationErrorDetail {
  field: string;
  message: string;
}

model InternalServerError {
  @statusCode _: 500;
}

model Unavailable {
  @statusCode _: 503;
}

model GatewayTimeout {
  @statusCode _: 504;
}

@friendlyName("ErrorResponse")
model ErrorResponse {
  message: string;
}

alias ServerError = InternalServerError | Unavailable | GatewayTimeout;
