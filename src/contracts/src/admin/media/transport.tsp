namespace IsekaiObservatory.Admin;

model MediaSearchResponse {
  media: Media[];
  maxPage: int32;
}

model MediaGetResponse {
  media: Media;
}

model MediaCreateRequest {
  title: mediaTitle;
  url: mediaUrl;
  typeValue: MediaTypeValue;
  formatValue: MediaFormatValue;
  isDisplay: boolean;
}

model MediaCreateResponse {
  media: Media;
}

model MediaUpdateRequest {
  title: mediaTitle;
  url: mediaUrl;
  typeValue: MediaTypeValue;
  formatValue: MediaFormatValue;
  isDisplay: boolean;
}

model MediaUpdateResponse {
  media: Media;
}
