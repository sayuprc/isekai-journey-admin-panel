import { describe, expect, it } from 'bun:test';
import { DEFAULT_AUTH_RETURN_TO, resolveAuthReturnTo } from './auth-redirect';

describe('resolveAuthReturnTo', () => {
  it('return_to がない場合は既定の遷移先を返す', () => {
    expect(resolveAuthReturnTo(null)).toBe(DEFAULT_AUTH_RETURN_TO);
    expect(resolveAuthReturnTo('')).toBe(DEFAULT_AUTH_RETURN_TO);
  });

  it('同一 origin のパスを返す', () => {
    expect(resolveAuthReturnTo('/songs?sort=title')).toBe('/songs?sort=title');
  });

  it('外部 URL と protocol-relative URL は既定の遷移先へ丸める', () => {
    expect(resolveAuthReturnTo('https://example.com/songs')).toBe(DEFAULT_AUTH_RETURN_TO);
    expect(resolveAuthReturnTo('//example.com/songs')).toBe(DEFAULT_AUTH_RETURN_TO);
  });
});
