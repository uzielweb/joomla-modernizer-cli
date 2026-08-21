<?php
/**
 * ============================================================================
 * UNIVERSAL JOOMLA CODE MODERNIZER (Joomla 3.x/4.x -> Joomla 5.x/6.x & PHP 8.4+)
 * ============================================================================
 *
 * Ferramenta universal para auditoria, sanitização e refatoração de código legado
 * em templates, overrides, componentes, módulos, plugins e bibliotecas do Joomla.
 *
 * USO:
 *   php joomla_modernizer.php [caminho] [opções]
 *
 * EXEMPLOS:
 *   php joomla_modernizer.php templates/shaper_helixultimate
 *   php joomla_modernizer.php components/com_custom
 *   php joomla_modernizer.php . --dry-run
 *   php joomla_modernizer.php . --backup
 * @author Uziel Almeida Oliveira via Ponto Mega
 * @copyright Copyright (C) Uziel Almeida Oliveira / Ponto Mega. Todos os direitos reservados.
 * @version 1.0.0
 * @license GNU General Public License v2 or later
 */

declare(strict_types=1);

namespace JoomlaModernizer;

class ModernizerEngine
{
    private string $targetPath;
    private bool $dryRun = false;
    private bool $createBackup = false;
    private bool $verbose = false;
    private array $stats = [
        'scanned'  => 0,
        'modified' => 0,
        'errors'   => 0,
        'rules'    => [],
    ];

    /**
     * Regras universais de substituição direta (1:1)
     */
    private array $directReplacements = [
        // CMS Factory
        'JFactory::getApplication()' => '\Joomla\CMS\Factory::getApplication()',
        'JFactory::getDbo()'          => '\Joomla\CMS\Factory::getDbo()',
        'JFactory::getDBO()'          => '\Joomla\CMS\Factory::getDbo()',
        'JFactory::getUser()'         => '\Joomla\CMS\Factory::getUser()',
        'JFactory::getDocument()'      => '\Joomla\CMS\Factory::getDocument()',
        'JFactory::getLanguage()'     => '\Joomla\CMS\Factory::getLanguage()',
        'JFactory::getConfig()'        => '\Joomla\CMS\Factory::getConfig()',
        'JFactory::getDate()'         => '\Joomla\CMS\Factory::getDate()',
        'JFactory::getURI()'          => '\Joomla\CMS\Uri\Uri::getInstance()',
        'JFactory::getMailer()'       => '\Joomla\CMS\Factory::getMailer()',
        'JFactory::getSession()'      => '\Joomla\CMS\Factory::getSession()',
        'JFactory::getFeedFactory()'  => '\Joomla\CMS\Factory::getFeedFactory()',

        // URIs
        'JURI::base()' => '\Joomla\CMS\Uri\Uri::base()',
        'JURI::root()' => '\Joomla\CMS\Uri\Uri::root()',
        'JUri::base()' => '\Joomla\CMS\Uri\Uri::base()',
        'JUri::root()' => '\Joomla\CMS\Uri\Uri::root()',
        'JURI::getInstance()' => '\Joomla\CMS\Uri\Uri::getInstance()',
        'JUri::getInstance()' => '\Joomla\CMS\Uri\Uri::getInstance()',

        // Idioma e Roteamento
        'JText::_'           => '\Joomla\CMS\Language\Text::_',
        'JText::sprintf'     => '\Joomla\CMS\Language\Text::sprintf',
        'JText::printf'      => '\Joomla\CMS\Language\Text::printf',
        'JText::script'      => '\Joomla\CMS\Language\Text::script',
        'JText::plural'      => '\Joomla\CMS\Language\Text::plural',
        'JRoute::_'          => '\Joomla\CMS\Router\Route::_',
        'JRoute::link'       => '\Joomla\CMS\Router\Route::link',

        // Layouts e HTML
        'JLayoutHelper::render' => '\Joomla\CMS\Layout\LayoutHelper::render',
        'JLayoutFile'           => '\Joomla\CMS\Layout\FileLayout',
        'JHtml::_'              => '\Joomla\CMS\HTML\HTMLHelper::_',
        'JHTML::_'              => '\Joomla\CMS\HTML\HTMLHelper::_',
        'JHtml::addIncludePath' => '\Joomla\CMS\HTML\HTMLHelper::addIncludePath',
        'JHTML::addIncludePath' => '\Joomla\CMS\HTML\HTMLHelper::addIncludePath',
        'JHtml::date'           => '\Joomla\CMS\HTML\HTMLHelper::date',
        'JHTML::date'           => '\Joomla\CMS\HTML\HTMLHelper::date',
        'JHtml::tooltipText'    => '\Joomla\CMS\HTML\HTMLHelper::tooltipText',
        'JHTML::tooltipText'    => '\Joomla\CMS\HTML\HTMLHelper::tooltipText',

        // Helpers de Componentes Core
        'ContentHelperRoute::getArticleRoute'  => '\Joomla\Component\Content\Site\Helper\RouteHelper::getArticleRoute',
        'ContentHelperRoute::getCategoryRoute' => '\Joomla\Component\Content\Site\Helper\RouteHelper::getCategoryRoute',
        'ContactHelperRoute::getContactRoute'  => '\Joomla\Component\Contact\Site\Helper\RouteHelper::getContactRoute',
        'NewsfeedsHelperRoute::getNewsfeedRoute' => '\Joomla\Component\Newsfeeds\Site\Helper\RouteHelper::getNewsfeedRoute',
        'BannerHelper::'                       => '\Joomla\Component\Banners\Site\Helper\BannerHelper::',
        'JLanguageAssociations::isEnabled()'   => '\Joomla\CMS\Language\Associations::isEnabled()',

        // Filesystem
        'JFolder::create' => '\Joomla\CMS\Filesystem\Folder::create',
        'JFolder::delete' => '\Joomla\CMS\Filesystem\Folder::delete',
        'JFolder::exists' => '\Joomla\CMS\Filesystem\Folder::exists',
        'JFolder::files'  => '\Joomla\CMS\Filesystem\Folder::files',
        'JFolder::folders'=> '\Joomla\CMS\Filesystem\Folder::folders',
        'JFile::copy'     => '\Joomla\CMS\Filesystem\File::copy',
        'JFile::delete'   => '\Joomla\CMS\Filesystem\File::delete',
        'JFile::exists'   => '\Joomla\CMS\Filesystem\File::exists',
        'JFile::read'     => '\Joomla\CMS\Filesystem\File::read',
        'JFile::write'    => '\Joomla\CMS\Filesystem\File::write',
        'JFile::upload'   => '\Joomla\CMS\Filesystem\File::upload',
        'JFile::stripExt' => '\Joomla\CMS\Filesystem\File::stripExt',
        'JFile::getExt'   => '\Joomla\CMS\Filesystem\File::getExt',
        'JPath::clean'    => '\Joomla\CMS\Filesystem\Path::clean',

        // Forms e Tables
        'JForm::addFormPath'  => '\Joomla\CMS\Form\Form::addFormPath',
        'JForm::addFieldPath' => '\Joomla\CMS\Form\Form::addFieldPath',
        'JTable::getInstance' => '\Joomla\CMS\Table\Table::getInstance',
    ];

    /**
     * Expressões Regulares avançadas para refatorações contextuais
     */
    private array $regexReplacements = [
        // JRequest::getVar / getInt / getString / getCmd / getBool -> $app->getInput()->get / getInt / etc.
        '/(?<!\w)JRequest::getVar\s*\(\s*([^,\)]+)\s*,\s*([^,\)]*)\s*,\s*[\'"][^\'"]*[\'"]\s*,\s*[\'"]([^\'"]*)[\'"]\s*\)/i' => '\Joomla\CMS\Factory::getApplication()->getInput()->get($1, $2, \'$3\')',
        '/(?<!\w)JRequest::getVar\s*\(\s*([^,\)]+)\s*,\s*([^,\)]+)\s*\)/i' => '\Joomla\CMS\Factory::getApplication()->getInput()->get($1, $2)',
        '/(?<!\w)JRequest::getVar\s*\(\s*([^,\)]+)\s*\)/i' => '\Joomla\CMS\Factory::getApplication()->getInput()->get($1)',
        '/(?<!\w)JRequest::getInt\s*\(\s*([^,\)]+)\s*,\s*([^,\)]+)\s*\)/i' => '\Joomla\CMS\Factory::getApplication()->getInput()->getInt($1, $2)',
        '/(?<!\w)JRequest::getInt\s*\(\s*([^,\)]+)\s*\)/i' => '\Joomla\CMS\Factory::getApplication()->getInput()->getInt($1, 0)',
        '/(?<!\w)JRequest::getString\s*\(\s*([^,\)]+)\s*,\s*([^,\)]+)\s*\)/i' => '\Joomla\CMS\Factory::getApplication()->getInput()->getString($1, $2)',
        '/(?<!\w)JRequest::getString\s*\(\s*([^,\)]+)\s*\)/i' => '\Joomla\CMS\Factory::getApplication()->getInput()->getString($1, \'\')',
        '/(?<!\w)JRequest::getCmd\s*\(\s*([^,\)]+)\s*,\s*([^,\)]+)\s*\)/i' => '\Joomla\CMS\Factory::getApplication()->getInput()->getCmd($1, $2)',
        '/(?<!\w)JRequest::getCmd\s*\(\s*([^,\)]+)\s*\)/i' => '\Joomla\CMS\Factory::getApplication()->getInput()->getCmd($1, \'\')',
        '/(?<!\w)JRequest::getBool\s*\(\s*([^,\)]+)\s*,\s*([^,\)]+)\s*\)/i' => '\Joomla\CMS\Factory::getApplication()->getInput()->getBool($1, $2)',
        '/(?<!\w)JRequest::getBool\s*\(\s*([^,\)]+)\s*\)/i' => '\Joomla\CMS\Factory::getApplication()->getInput()->getBool($1, false)',
        '/(?<!\w)JRequest::setVar\s*\(\s*([^,\)]+)\s*,\s*([^,\)]+)\s*\)/i' => '\Joomla\CMS\Factory::getApplication()->getInput()->set($1, $2)',
        '/(?<!\w)JRequest::getMethod\s*\(\s*\)/i' => '\Joomla\CMS\Factory::getApplication()->getInput()->getMethod()',

        // Remoção de comportamentos obsoletos extintos no J4/5/6
        '/(\\\?Joomla\\\CMS\\\HTML\\\HTMLHelper|JHtml|JHTML)::_\s*\(\s*[\'"]behavior\.(caption|modal|tooltip|noframes)[\'"]\s*\)\s*;/i' => '/* Removido $0 (Obsoleto no Joomla 4/5/6 - gerenciado via Bootstrap 5/WAM) */',

        // JError::raiseError -> throw new Exception
        '/(?<!\w)JError::raiseError\s*\(\s*(\d+)\s*,\s*(.*?)\s*\)\s*;/i' => 'throw new \Exception($2, $1);',

        // Sintaxe de arrays com chaves legada do PHP 7.x ($str{0} -> $str[0])
        '/(\$[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)\{(\d+|\$[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)\}/' => '$1[$2]',
    ];

    /**
     * Diretórios e extensões que devem ser ignorados
     */
    private array $ignoredPaths = [
        'vendor',
        'node_modules',
        'libraries/vendor',
        'media/vendor',
        'cache',
        'administrator/cache',
        'logs',
        'tmp',
        '.git',
    ];

    public function __construct(string $targetPath, array $options = [])
    {
        $this->targetPath   = $this->resolvePath($targetPath);
        $this->dryRun       = !empty($options['dry-run']);
        $this->createBackup = !empty($options['backup']);
        $this->verbose      = !empty($options['verbose']);
    }

    public function run(): void
    {
        $this->printHeader();

        if (!is_dir($this->targetPath) && !is_file($this->targetPath)) {
            $this->printError("O caminho especificado não existe: {$this->targetPath}");
            exit(1);
        }

        $files = $this->collectPhpFiles($this->targetPath);
        $total = count($files);

        $this->printInfo("Iniciando varredura em {$total} arquivo(s) PHP...");
        echo "\n";

        foreach ($files as $file) {
            $this->processFile($file);
        }

        $this->printSummary();
    }

    private function processFile(string $filePath): void
    {
        $this->stats['scanned']++;
        $content = file_get_contents($filePath);
        if ($content === false || empty($content)) {
            return;
        }

        $original = $content;
        $matchedRules = [];

        // 1. Substituições diretas 1:1
        foreach ($this->directReplacements as $search => $replace) {
            if (str_contains($content, $search)) {
                $count = substr_count($content, $search);
                $content = str_replace($search, $replace, $content);
                $matchedRules[$search] = ($matchedRules[$search] ?? 0) + $count;
            }
        }

        // 2. Substituições com Expressões Regulares
        foreach ($this->regexReplacements as $pattern => $replacement) {
            $newContent = preg_replace($pattern, $replacement, $content, -1, $count);
            if ($count > 0 && $newContent !== null) {
                $content = $newContent;
                $matchedRules[$pattern] = ($matchedRules[$pattern] ?? 0) + $count;
            }
        }

        // Se o arquivo sofreu alterações
        if ($content !== $original) {
            $this->stats['modified']++;

            foreach ($matchedRules as $rule => $count) {
                $this->stats['rules'][$rule] = ($this->stats['rules'][$rule] ?? 0) + $count;
            }

            $relPath = $this->getRelativePath($filePath);

            if ($this->verbose || $this->dryRun) {
                $this->printSuccess("[MODIFICADO] {$relPath} (" . array_sum($matchedRules) . " ocorrências)");
            }

            if (!$this->dryRun) {
                // Validação de sintaxe antes de salvar
                if (!$this->validateSyntax($content, $filePath)) {
                    $this->stats['errors']++;
                    $this->printError("[FALHA LINT] Sintaxe inválida gerada para: {$relPath}. Alteração cancelada.");
                    return;
                }

                if ($this->createBackup) {
                    file_put_contents($filePath . '.bak', $original);
                }

                file_put_contents($filePath, $content);
            }
        }
    }

    /**
     * Valida sintaxe PHP temporária antes de gravar no arquivo
     */
    private function validateSyntax(string $phpCode, string $originalPath): bool
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'jmod_');
        if (!$tmpFile) {
            return true;
        }

        file_put_contents($tmpFile, $phpCode);
        $output = [];
        $returnVar = 0;
        exec("php -l \"{$tmpFile}\" 2>&1", $output, $returnVar);
        @unlink($tmpFile);

        return $returnVar === 0;
    }

    private function collectPhpFiles(string $path): array
    {
        if (is_file($path)) {
            return str_ends_with($path, '.php') ? [$path] : [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $pathname = str_replace('\\', '/', $file->getPathname());

            // Ignorar pastas de bibliotecas de terceiros ou temporárias
            $ignore = false;
            foreach ($this->ignoredPaths as $ignored) {
                if (str_contains($pathname, '/' . $ignored . '/') || str_starts_with($pathname, $ignored . '/')) {
                    $ignore = true;
                    break;
                }
            }

            if (!$ignore) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function resolvePath(string $path): string
    {
        $resolved = realpath($path);
        if ($resolved !== false) {
            return $resolved;
        }

        // Tenta relativo ao diretório de execução
        $cwd = getcwd();
        $combined = $cwd . DIRECTORY_SEPARATOR . $path;
        $resolved = realpath($combined);

        return $resolved !== false ? $resolved : $path;
    }

    private function getRelativePath(string $fullPath): string
    {
        $base = getcwd() ?: '';
        $full = str_replace('\\', '/', $fullPath);
        $base = str_replace('\\', '/', $base);

        if (str_starts_with($full, $base)) {
            return ltrim(substr($full, strlen($base)), '/');
        }

        return $fullPath;
    }

    private function printHeader(): void
    {
        echo "\033[1;36m=================================================================\033[0m\n";
        echo "\033[1;32m      UNIVERSAL JOOMLA MODERNIZER (J3/J4 -> J5/J6 & PHP 8.4)     \033[0m\n";
        echo "\033[1;36m=================================================================\033[0m\n";
        echo " Alvo:       \033[1;33m{$this->targetPath}\033[0m\n";
        echo " Modo:       " . ($this->dryRun ? "\033[1;35m[DRY-RUN - Apenas Simulação]\033[0m" : "\033[1;32m[EXECUÇÃO REAL]\033[0m") . "\n";
        echo " Backups:    " . ($this->createBackup ? "\033[1;32mSIM (.bak)\033[0m" : "NÃO") . "\n";
        echo " Verbose:    " . ($this->verbose ? "SIM" : "NÃO") . "\n";
        echo "-----------------------------------------------------------------\n";
    }

    private function printSummary(): void
    {
        echo "\n\033[1;36m======================= RESUMO DA EXECUÇÃO ======================\033[0m\n";
        echo " Total de arquivos PHP escaneados: \033[1;37m{$this->stats['scanned']}\033[0m\n";
        echo " Total de arquivos refatorados:    \033[1;32m{$this->stats['modified']}\033[0m\n";
        echo " Erros de sintaxe prevenidos:      \033[1;31m{$this->stats['errors']}\033[0m\n";
        echo "-----------------------------------------------------------------\n";

        if (!empty($this->stats['rules'])) {
            echo "\033[1;33mRegras mais aplicadas:\033[0m\n";
            arsort($this->stats['rules']);
            $top = array_slice($this->stats['rules'], 0, 15, true);
            foreach ($top as $rule => $count) {
                $shortRule = strlen($rule) > 55 ? substr($rule, 0, 52) . '...' : $rule;
                printf("  %-55s : \033[1;32m%d\033[0m\n", $shortRule, $count);
            }
        }

        echo "\033[1;36m=================================================================\033[0m\n";
        if ($this->dryRun) {
            echo "\033[1;35mNenhum arquivo foi modificado no disco (Modo Dry-Run).\033[0m\n";
            echo "Para aplicar as alterações de verdade, execute sem o argumento --dry-run\n";
        } else {
            echo "\033[1;32m✓ Refatoração concluída com sucesso e sintaxe validada!\033[0m\n";
            echo "Lembre-se de limpar o cache do Joomla: \033[1;33mphp cli/joomla.php cache:clean\033[0m\n";
        }
        echo "\n";
    }

    private function printSuccess(string $msg): void
    {
        echo "\033[32m{$msg}\033[0m\n";
    }

    private function printError(string $msg): void
    {
        echo "\033[31m{$msg}\033[0m\n";
    }

    private function printInfo(string $msg): void
    {
        echo "\033[36m{$msg}\033[0m\n";
    }
}

// -----------------------------------------------------------------------------
// CLI ENTRYPOINT
// -----------------------------------------------------------------------------
if (PHP_SAPI !== 'cli') {
    die("Este script só pode ser executado via linha de comando (CLI).\n");
}

$args = $argv;
array_shift($args); // Remove o nome do script

$options = [
    'dry-run' => false,
    'backup'  => false,
    'verbose' => false,
];

$target = '.';

foreach ($args as $arg) {
    if ($arg === '--dry-run') {
        $options['dry-run'] = true;
    } elseif ($arg === '--backup') {
        $options['backup'] = true;
    } elseif ($arg === '--verbose' || $arg === '-v') {
        $options['verbose'] = true;
    } elseif (!str_starts_with($arg, '-')) {
        $target = $arg;
    }
}

$engine = new ModernizerEngine($target, $options);
$engine->run();
