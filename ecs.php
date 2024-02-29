<?php

use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\EmptyStatementSniff;
use PhpCsFixer\Fixer\Basic\BracesFixer;
use PhpCsFixer\Fixer\Basic\CurlyBracesPositionFixer;
use PhpCsFixer\Fixer\Whitespace\IndentationTypeFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {
	$config->import(__DIR__ . '/vendor/buckhamduffy/coding-standards/ecs.php');
    $config->indentation('spaces');
	$config->paths([
        __DIR__ . '/src',
        __DIR__ . '/config',
        __DIR__ . '/database',
    ]);
};
