<?php
// This file is not a CODE, it makes no sense and won't run or validate
// Its AST serves PHPStorm IDE as DATA source to make advanced type inference decisions.
// @codingStandardsIgnoreFile
namespace PHPSTORM_META {

    $STATIC_METHOD_TYPES = [
        \Symfony\Component\Console\Helper\HelperSet::get('') => [
            'php_bin_get' instanceof \Rikby\Console\Helper\PhpBinHelper,
            'simple_question' instanceof \Rikby\Console\Helper\SimpleQuestionHelper,
            'git_dir_get' instanceof \Rikby\Console\Helper\GitDirHelper,
            'question' instanceof \Symfony\Component\Console\Helper\SymfonyQuestionHelper,
            'process' instanceof \Symfony\Component\Console\Helper\ProcessHelper,
            'debug_formatter' instanceof \Symfony\Component\Console\Helper\DebugFormatterHelper,
            'descriptor' instanceof \Symfony\Component\Console\Helper\DescriptorHelper,
            'code_validator' instanceof \PreCommit\Console\Helper\ValidatorHelper,
            'commithook_config' instanceof \PreCommit\Console\Helper\ConfigHelper,
            'commithook_config_set' instanceof \PreCommit\Console\Helper\Config\SetHelper,
            'commithook_config_writer' instanceof \PreCommit\Console\Helper\Config\WriterHelper,
        ],
    ];
}
