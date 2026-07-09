import { createHash, timingSafeEqual } from 'node:crypto';

/**
 * CSRF トークンを定数時間で比較する。
 *
 * 単純な `===` / `!==` は一致した先頭文字数に比例して処理時間が変わり、
 * トークンを 1 文字ずつ推測されうる。長さの差による早期 return (長さオラクル) も
 * 避けるため、固定長の SHA-256 ハッシュへ変換してから timingSafeEqual で比較する。
 */
export const isCsrfTokenMatch = (expected: string, actual: string): boolean => {
  const hash = (value: string): Buffer => createHash('sha256').update(value).digest();

  return timingSafeEqual(hash(expected), hash(actual));
};

if (import.meta.vitest) {
  const { describe, expect, it } = import.meta.vitest;

  describe('isCsrfTokenMatch', () => {
    it('一致するトークンで true を返す', () => {
      expect(isCsrfTokenMatch('csrf-token-value', 'csrf-token-value')).toBe(true);
    });

    it('同じ長さの異なるトークンで false を返す', () => {
      expect(isCsrfTokenMatch('csrf-token-aaaa', 'csrf-token-bbbb')).toBe(false);
    });

    it('長さの異なるトークンでも例外を投げず false を返す', () => {
      expect(isCsrfTokenMatch('short', 'a-much-longer-csrf-token-value')).toBe(false);
    });

    it('空文字と非空文字で false を返す', () => {
      expect(isCsrfTokenMatch('', 'non-empty')).toBe(false);
    });
  });
}
