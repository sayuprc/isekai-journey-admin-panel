<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/bootstrap',
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/lang',
        __DIR__ . '/public',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
        __DIR__ . '/artisan',
    ])
    ->withRootFiles()
    ->withSpacing(Option::INDENTATION_SPACES, PHP_EOL)
    ->withPhpCsFixerSets(psr2: true, psr12: true)
    ->withRules([
        \PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer::class,
        \PhpCsFixer\Fixer\ArrayNotation\NoMultilineWhitespaceAroundDoubleArrowFixer::class,
        \PhpCsFixer\Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer::class,
        \PhpCsFixer\Fixer\ArrayNotation\TrimArraySpacesFixer::class,
        \PhpCsFixer\Fixer\Basic\NoTrailingCommaInSinglelineFixer::class,
        \PhpCsFixer\Fixer\Casing\MagicConstantCasingFixer::class,
        \PhpCsFixer\Fixer\Casing\MagicMethodCasingFixer::class,
        \PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer::class,
        \PhpCsFixer\Fixer\Casing\NativeTypeDeclarationCasingFixer::class,
        \PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer::class,
        \PhpCsFixer\Fixer\ClassNotation\OrderedInterfacesFixer::class,
        \PhpCsFixer\Fixer\ClassNotation\OrderedTraitsFixer::class,
        \PhpCsFixer\Fixer\ClassNotation\OrderedTypesFixer::class,
        \PhpCsFixer\Fixer\Comment\MultilineCommentOpeningClosingFixer::class,
        \PhpCsFixer\Fixer\Comment\NoEmptyCommentFixer::class,
        \PhpCsFixer\Fixer\Comment\SingleLineCommentSpacingFixer::class,
        \PhpCsFixer\Fixer\Comment\SingleLineCommentStyleFixer::class,
        \PhpCsFixer\Fixer\ControlStructure\IncludeFixer::class,
        \PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer::class,
        \PhpCsFixer\Fixer\ControlStructure\SimplifiedIfReturnFixer::class,
        \PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer::class,
        \PhpCsFixer\Fixer\FunctionNotation\LambdaNotUsedImportFixer::class,
        \PhpCsFixer\Fixer\FunctionNotation\NullableTypeDeclarationForDefaultNullValueFixer::class,
        \PhpCsFixer\Fixer\Import\NoUnusedImportsFixer::class,
        \PhpCsFixer\Fixer\LanguageConstruct\ExplicitIndirectVariableFixer::class,
        \PhpCsFixer\Fixer\LanguageConstruct\NullableTypeDeclarationFixer::class,
        \PhpCsFixer\Fixer\LanguageConstruct\SingleSpaceAroundConstructFixer::class,
        \PhpCsFixer\Fixer\NamespaceNotation\NoLeadingNamespaceWhitespaceFixer::class,
        \PhpCsFixer\Fixer\Operator\NoUselessConcatOperatorFixer::class,
        \PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer::class,
        \PhpCsFixer\Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer::class,
        \PhpCsFixer\Fixer\Operator\OperatorLinebreakFixer::class,
        \PhpCsFixer\Fixer\PhpTag\LinebreakAfterOpeningTagFixer::class,
        \PhpCsFixer\Fixer\PhpUnit\PhpUnitFqcnAnnotationFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocAddMissingParamAnnotationFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocIndentFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocInlineTagNormalizerFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocOrderByValueFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocOrderFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocParamOrderFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocReturnSelfReferenceFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocScalarFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocSingleLineVarSpacingFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocTagCasingFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocTrimConsecutiveBlankLineSeparationFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocTrimFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocTypesFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocVarAnnotationCorrectOrderFixer::class,
        \PhpCsFixer\Fixer\ReturnNotation\ReturnAssignmentFixer::class,
        \PhpCsFixer\Fixer\Semicolon\MultilineWhitespaceBeforeSemicolonsFixer::class,
        \PhpCsFixer\Fixer\Semicolon\NoEmptyStatementFixer::class,
        \PhpCsFixer\Fixer\Semicolon\NoSinglelineWhitespaceBeforeSemicolonsFixer::class,
        \PhpCsFixer\Fixer\Semicolon\SemicolonAfterInstructionFixer::class,
        \PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer::class,
        \PhpCsFixer\Fixer\Strict\StrictComparisonFixer::class,
        \PhpCsFixer\Fixer\StringNotation\ExplicitStringVariableFixer::class,
        \PhpCsFixer\Fixer\StringNotation\SimpleToComplexStringVariableFixer::class,
        \PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer::class,
        \PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer::class,
        \PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer::class,
        \PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer::class,
        \PhpCsFixer\Fixer\Whitespace\NoSpacesAroundOffsetFixer::class,
        \PhpCsFixer\Fixer\Whitespace\TypeDeclarationSpacesFixer::class,
        \PhpCsFixer\Fixer\Whitespace\TypesSpacesFixer::class,
    ])
    ->withConfiguredRule(\PhpCsFixer\Fixer\ArrayNotation\WhitespaceAfterCommaInArrayFixer::class, [
        'ensure_single_space' => true,
    ])
    ->withConfiguredRule(\PhpCsFixer\Fixer\CastNotation\CastSpacesFixer::class, [
        'space' => 'none',
    ])
    ->withConfiguredRule(\PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer::class, [
        'operators' => [
            '=>' => 'single_space',
            '||' => 'align_single_space_minimal',
            '&&' => 'align_single_space_minimal',
        ],
    ])
    ->withConfiguredRule(\PhpCsFixer\Fixer\Operator\ConcatSpaceFixer::class, [
        'spacing' => 'one',
    ])
    ->withConfiguredRule(\PhpCsFixer\Fixer\Phpdoc\PhpdocTypesOrderFixer::class, [
        'null_adjustment' => 'always_last',
        'sort_algorithm' => 'none',
    ])
    ->withConfiguredRule(\PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer::class, [
        'import_symbols' => true,
        'leading_backslash_in_global_namespace' => true,
    ])
    ->withConfiguredRule(\PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer::class, [
        'tokens' => [
            'attribute',
            'break',
            'case',
            'continue',
            'curly_brace_block',
            'default',
            'extra',
            'parenthesis_brace_block',
            'return',
            'square_brace_block',
            'switch',
            'throw',
            'use',
            'use_trait',
        ],
    ])
    ->withSkip([
        __DIR__ . '/bootstrap/cache',

        \PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer::class => [
            __DIR__ . '/ecs.php',
            __DIR__ . '/bootstrap/providers.php',
        ],
    ]);
