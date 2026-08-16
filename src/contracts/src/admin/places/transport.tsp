import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Admin;

model PlaceListResponse {
  places: Place[];
}

model PlaceSearchResponse {
  places: Place[];
  maxPage: int32;
}

model PlaceGetResponse {
  place: Place;
}

model PlaceCreateRequest {
  name: placeName;
  kindValue: PlaceKindValue;
}

model PlaceCreateResponse {
  place: Place;
}

model PlaceUpdateRequest {
  name: placeName;
  kindValue: PlaceKindValue;
}

model PlaceUpdateResponse {
  place: Place;
}
