# Universal Joomla Code Modernizer (CLI)

[![Joomla 5](https://img.shields.io/badge/Joomla-5.x-blue.svg)](https://www.joomla.org/)
[![Joomla 6](https://img.shields.io/badge/Joomla-6.x-blueviolet.svg)](https://www.joomla.org/)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B%20%7C%208.3%20%7C%208.4%20%7C%208.5-8892BF.svg)](https://www.php.net/)
[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

Ferramenta CLI universal e portátil desenvolvida para automatizar a auditoria, higienização e refatoração de código PHP legado (Joomla 3.x / 4.x) para os padrões modernos do **Joomla 5.x, Joomla 6.x e PHP 8.4+**.

---

## 🚀 Funcionalidades

- **Zero Dependências:** Funciona imediatamente via PHP CLI em qualquer servidor ou ambiente local (Laragon, Docker, Linux, macOS, Windows).
- **Substituição de Classes 1:1 Seguras:**
  - `JFactory::*` ➔ `\Joomla\CMS\Factory::*`
  - `JText::*` ➔ `\Joomla\CMS\Language\Text::*`
  - `JRoute::*` ➔ `\Joomla\CMS\Router\Route::*`
  - `JURI::*` / `JUri::*` ➔ `\Joomla\CMS\Uri\Uri::*`
  - `JHtml::*` / `JHTML::*` ➔ `\Joomla\CMS\HTML\HTMLHelper::*`
  - `JLayoutHelper::render` ➔ `\Joomla\CMS\Layout\LayoutHelper::render`
  - `JFolder::*`, `JFile::*`, `JPath::*` ➔ `\Joomla\CMS\Filesystem\*`
  - `ContentHelperRoute::getArticleRoute` ➔ `\Joomla\Component\Content\Site\Helper\RouteHelper::getArticleRoute`
- **Refatoração Inteligente via Regex:**
  - `JRequest::getVar / getInt / getString / setVar` ➔ `\Joomla\CMS\Factory::getApplication()->getInput()->*`
  - `JError::raiseError(500, ...)` ➔ `throw new \Exception(...)`
  - Remoção automática de comportamentos extintos no Joomla 5/6 (`behavior.caption`, `behavior.modal`, `behavior.tooltip`).
  - Correção de chaves de array legadas do PHP 7 (`$var{0}` ➔ `$var[0]`).
- **Prevenção de Quebras de Código (Lint Integrado):** Executa validação de sintaxe (`php -l`) em tempo real antes de persistir qualquer alteração no disco. Se a sintaxe for inválida, a alteração é abortada com alerta.
- **Simulação Segura (`--dry-run`):** Permite inspecionar todas as alterações antes de aplicá-las.
- **Criação de Backups (`--backup`):** Gera arquivos `.bak` para rollback instantâneo.

---

## 📦 Como Usar

### 1. Download Rápido
Baixe o script diretamente na raiz do seu projeto Joomla:
```bash
curl -O https://raw.githubusercontent.com/intercodebrasil/joomla-modernizer-cli/main/joomla_modernizer.php
```
*(ou simplesmente copie o arquivo `joomla_modernizer.php` para onde desejar)*

---

### 2. Exemplos de Execução

#### Simulação (Dry-Run) sem alterar arquivos:
```bash
php joomla_modernizer.php templates/meu_template --dry-run
```

#### Refatorar um template ou pasta de overrides:
```bash
php joomla_modernizer.php templates/shaper_helixultimate
```

#### Refatorar um componente ou plugin customizado:
```bash
php joomla_modernizer.php components/com_meucomponente
php joomla_modernizer.php plugins/system/meuplugin
```

#### Refatorar o projeto inteiro criando backups automáticos:
```bash
php joomla_modernizer.php . --backup
```

#### Modo detalhado (Verbose):
```bash
php joomla_modernizer.php . --verbose
```

---

## 🧹 Pós-Execução

Após executar a modernização, limpe o cache do Joomla:
```bash
php cli/joomla.php cache:clean
```

---

## 📄 Licença

Distribuído sob a licença GNU General Public License v2 (GPL-2.0). Veja `LICENSE` para mais detalhes.
