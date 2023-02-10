<?php

namespace Alexconesap\Commons\Tools;

use Symfony\Component\Finder\Finder;

/**
 * #Class discovery for educational purposes.
 *
 * ###Code examples
 *
 * - Discover PHP Classes in the Marketing/CampaignProcessors sub folder of Your Laravel App Path Root.
 * <code>
 * $classes_found = ClassFinder::findClasses(
 *      app_path('Marketing/CampaignProcessors'),
 *      false
 * );
 * foreach ($classes_found as $one_class) {
 *     $obj = new $one_class;
 *     // ...
 * }
 * </code>
 *
 * Filtering only classes that implements a given interface using a Laravel Collection:
 * <code>
 * $classes_found = collect(ClassFinder::findClasses(app_path('Marketing/CampaignProcessors')))
 * ->filter(function ($className) {
 *     return is_subclass_of($className, CampaignProcessorInterface::class);
 * });
 * </code>
 *
 * - Discover PHP Classes in the Marketing/CampaignProcessors sub folder of Your Laravel App Path Root.
 * <code>
 * $classes_found = ClassFinder::findClasses(
 *      app_path('Marketing/CampaignProcessors'),
 *      false
 * );
 * foreach ($classes_found as $one_class) {
 *     $obj = new $one_class;
 *     // ...
 * }
 *
 * </code>
 *
 * @author Yakuma, 2020 (alexconesap@gmail.com)
 */
class ClassFinder
{
    /**
     * Find all the class and interface names in a given directory.
     *
     * @param string $directory
     * @param bool $include_interfaces (False)
     * @return array
     */
    public static function findClasses(string $directory, bool $include_interfaces = false): array
    {
        $classes = [];

        foreach (Finder::create()->in($directory)->name('*.php') as $file) {
            $classes[] = self::findClass($file->getRealPath(), $include_interfaces);
        }

        return array_filter($classes);
    }

    /**
     * Returns the class name including its namespace for a given PHP class file $php_class_filename.
     * Returns null when not found or when it does not match the given parameters criteria.
     *
     * It internally parses the given file.
     *
     * @param string $php_class_filename Full path to a PHP class file
     * @param bool $accept_interfaces Set to true to process interfaces as well
     * @return string|null
     */
    public static function findClass(string $php_class_filename, bool $accept_interfaces): ?string
    {
        $namespace = null;
        $tokens = token_get_all(file_get_contents($php_class_filename));

        foreach ($tokens as $row_num => $token) {
            if (self::tokenIsNamespace($token)) {
                $namespace = self::getNamespace($row_num + 2, $tokens);
            }

            if ($type = self::tokenIsClassOrInterface($token)) {
                if (!$accept_interfaces && $type == 'interface') {
                    return null;
                }
                return ltrim($namespace . '\\' . self::getClass($row_num + 2, $tokens));
            }
        }
        return null;
    }

    /**
     * Find the namespace in the tokens starting at a given key.
     *
     * @param int $start_token_key
     * @param array $tokens
     *
     * @return string|null
     */
    protected static function getNamespace(int $start_token_key, array $tokens): ?string
    {
        $namespace = '';
        $tokenCount = count($tokens);

        for ($i = $start_token_key; $i < $tokenCount; $i++) {
            if ($tokens[$i] == ';') {
                return '\\' . $namespace;
            }
            $namespace .= $tokens[$i][1] != "\n" ? $tokens[$i][1] : '';
        }

        return null;
    }

    /**
     * Find the class in the tokens starting at a given key.
     *
     * @param int $start_at_key
     * @param array $tokens
     *
     * @return string|null
     */
    protected static function getClass(int $start_at_key, array $tokens): ?string
    {
        $class = null;
        $tokenCount = count($tokens);

        $i = $start_at_key;
        while (self::isPartOfName($tokens[$i]) && $i < $tokenCount) {
            $class .= $tokens[$i][1];
            $i++;
        }
        return $class;
    }

    protected static function isPartOfName(array|string $token): bool
    {
        if (!is_array($token)) {
            return $token != ' ' && $token != '{';
        }
        return !empty(trim($token[1])) && $token[1] != "\n";
    }

    /**
     * Determine if the given token is a namespace keyword.
     * @param string|array $token
     * @return bool
     */
    protected static function tokenIsNamespace(string|array $token): bool
    {
        return is_array($token) && $token[1] == 'namespace';
    }

    /**
     * Determine if the given token is a class or interface keyword.
     *
     * @param string|array $token
     * @return string|bool
     */
    protected static function tokenIsClassOrInterface(string|array $token): string|bool
    {
        return is_array($token) && ($token[1] === 'class' || $token[1] === 'interface')
            ? $token[1] : false;
    }
}
