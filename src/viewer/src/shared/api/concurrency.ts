import pLimit from 'p-limit';

// ビルド時、getStaticPaths が詳細取得 (repository.get) を一斉に並列化するため、
// API への同時リクエスト数を制限してサーバー側の負荷 (429) を抑える。
const MAX_CONCURRENT_REQUESTS = 2;

// 全リポジトリで共有し、ビルド全体での同時実行数を上限に保つ。
export const apiLimit = pLimit(MAX_CONCURRENT_REQUESTS);
