namespace IsekaiObservatory.Admin;

model MediaSearchResponse {
  media: Media[];
  maxPage: int32;
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
