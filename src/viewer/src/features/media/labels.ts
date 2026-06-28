export const VIDEO_TYPES = ['mv', 'live-clip', 'interview', 'short'] as const;
export const POST_TYPES = ['tweet', 'instagram', 'youtube-community', 'blog'] as const;

export type VideoType = (typeof VIDEO_TYPES)[number];
export type PostType = (typeof POST_TYPES)[number];

export function isVideo(type: string): type is VideoType {
  return VIDEO_TYPES.includes(type as VideoType);
}

export function isPost(type: string): type is PostType {
  return POST_TYPES.includes(type as PostType);
}

export function mediaTypeLabel(type: string): string {
  return (
    {
      'mv': 'Music Video',
      'live-clip': 'Live Clip',
      'interview': 'Interview',
      'short': 'Short',
      'tweet': 'X / Twitter',
      'instagram': 'Instagram',
      'youtube-community': 'YT Community',
      'blog': 'Blog',
    }[type] ?? type
  );
}

// 保存済みサムネイル URL(末尾 sddefault.jpg)のファイル名のみを差し替えて
// 解像度バリエーションを srcset として生成する。maxres は欠落しがちなので使わない。
export function youtubeThumbnailSrcset(thumbnailUrl: string): string {
  const variants: Array<{ file: string; width: number }> = [
    { file: 'mqdefault.jpg', width: 320 },
    { file: 'hqdefault.jpg', width: 480 },
    { file: 'sddefault.jpg', width: 640 },
  ];

  return variants.map(({ file, width }) => `${thumbnailUrl.replace(/[^/]+$/, file)} ${width}w`).join(', ');
}
