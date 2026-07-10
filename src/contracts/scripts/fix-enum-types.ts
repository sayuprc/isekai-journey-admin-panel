import { readFileSync, writeFileSync } from 'node:fs';

export function fixEnumTypes(content: string): string {
  return content.replace(
    /^(\s+)type: number(\n\s+enum:)/gm,
    '$1type: integer\n$1format: int32$2',
  );
}

if (import.meta.main) {
  const { Glob } = await import('bun');
  const glob = new Glob('generated/oas/**/*.yaml');

  for await (const file of glob.scan('.')) {
    try {
      const content = readFileSync(file, 'utf-8');
      const fixed = fixEnumTypes(content);

      if (fixed !== content) {
        writeFileSync(file, fixed, 'utf-8');
        console.log(`Fixed numeric enum types in ${file}`);
      }
    } catch (err) {
      console.error(`Failed to process ${file}:`, err);
      process.exit(1);
    }
  }
}

if (import.meta.vitest) {
  const { describe, expect, test } = import.meta.vitest;

  describe('fixEnumTypes', () => {
    test('converts type: number followed by enum: to type: integer with format: int32', () => {
      const input = `    SomeValue:
      type: number
      enum:
        - 1
        - 2
`;
      const expected = `    SomeValue:
      type: integer
      format: int32
      enum:
        - 1
        - 2
`;
      expect(fixEnumTypes(input)).toBe(expected);
    });

    test('does not modify type: number that is not followed by enum:', () => {
      const input = `    SomeValue:
      type: number
      description: a plain number
`;
      expect(fixEnumTypes(input)).toBe(input);
    });

    test('does not modify type: string enum:', () => {
      const input = `    SomeValue:
      type: string
      enum:
        - foo
        - bar
`;
      expect(fixEnumTypes(input)).toBe(input);
    });

    test('handles multiple numeric enums in one file', () => {
      const input = `    RoleValue:
      type: number
      enum:
        - 1
        - 2
    SongTypeValue:
      type: number
      enum:
        - 1
        - 2
        - 3
`;
      const result = fixEnumTypes(input);
      expect(result).not.toContain('type: number');
      expect(result.match(/type: integer/g)).toHaveLength(2);
      expect(result.match(/format: int32/g)).toHaveLength(2);
    });

    test('preserves indentation level', () => {
      const input = `        DeepValue:
          type: number
          enum:
            - 1
`;
      const result = fixEnumTypes(input);
      expect(result).toContain('          type: integer');
      expect(result).toContain('          format: int32');
      expect(result).toContain('          enum:');
    });
  });
}
