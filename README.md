Digital Garden Gotenberg Bundle.
================================

Installation guide
------------------

To install this bundle, just do :
```shell
composer require dgarden/gotenberg-bundle
```

If you're in a symfony project with `config/routes` and `config/packages`, configuration
has to be created, if not you can copy/paste these configurations :

```yaml
# config/packages/dgarden_gotenberg.yaml
dgarden:
  gotenberg:
    output_path: '%kernel.project_dir%/var/pdf'
```

```yaml
# config/routes/dgarden_gotenberg.yaml
dgarden_gotenberg:
  resource: '@DigitalGardenGotenbergBundle/config/routes.php'
```

With flex enabled, you should also have [Sensiolabs Gotenberg bundle](https://github.com/sensiolabs/GotenbergBundle) 
configuration file created : 

```yaml
# config/packages/gotenberg.yaml

framework:
    http_client:
        scoped_clients:
            gotenberg.client:
                base_uri: '%env(GOTENBERG_DSN)%'

sensiolabs_gotenberg:
    http_client: 'gotenberg.client'
#    default_options:
#        pdf:
#            html:
#                metadata:
#                    Keywords: 'Symfony'

```

Configuration
-------------

Digital Garden Gotenberg Bundle's configuration :
```yaml
dgarden:
  gotenberg:
    output_path: 'string'  // Specify the default output for generated pdf files.
```

Check also the [Sensiolabs Gotenberg bundle](https://github.com/sensiolabs/GotenbergBundle) documentation to
know how to configure the connexion to the Gotenberg API, but mainly you'll just have to set the environment
variable `GOTENBERG_DSN`.

For example (set has default env variable in `config/packages/gotenberg.yaml`):

```yaml
# config/packages/gotenberg.yaml
parameters:
  env(GOTENBERG_DSN): 'https://gotenberg:gotenberg@gotenberg.gulfstream-group.fr'

# ...
```

Service PdfFileGenerator
------------------------

This bundle add to the container a new service `dgarden.gotenberg.generator` or 
̀`DigitalGarden\GotenbergBundle\Generator\PdfFileGeneratorInterface` with the following
helpers :

* `html(string $html, string $output, PdfFileGeneratorOptions|array $options = PdfFileGeneratorOptions::DEFAULT): SplFileInfo;` :
  Generate a PDF file from HTML.

  Example:
  ```php
  <?php
    // ...
    $file = $this->pdfFileGenerator->html('<html><body><h1>Hello</h1></body></html>', 'test.pdf');
  ```
* `htmlFile(string $file, string $output, PdfFileGeneratorOptions|array $options = PdfFileGeneratorOptions::DEFAULT): SplFileInfo` :
  Generate a PDF file from an HTML file.

  Example:
  ```php
  <?php
    // ...
    $file = $this->pdfFileGenerator->htmlFile('index.html', 'test.pdf');
  ```
* `merge(string $output, string ...$paths): SplFileInfo`: 
  Merge several PDF files into one (with default options). The last file name given
  is used as the output file.

  Example:
  ```php
  <?php
    // ...
    $file = $this->pdfFileGenerator->merge('file1.pdf', 'file2.pdf', 'file3.pdf', 'result.pdf');
  ```
* `mergeWithOptions(PdfFileGeneratorOptions|array $options, string $output, string ...$paths): SplFileInfo`:
  Merge several PDF files into one (with options).

  Example:
  ```php
  <?php
    // ...
    $file = $this->pdfFileGenerator->mergeWithOptions([PdfFileGeneratorOptions::OPTION_ASYNC => true], 'file1.pdf', 'file2.pdf', 'file3.pdf', 'result.pdf');
  ```
* `template(string $template, string $output, array $context = [], PdfFileGeneratorOptions|array $options = PdfFileGeneratorOptions::DEFAULT): SplFileInfo`:
  Generate a PDF file from a template.

  Example:
  ```php
  <?php
    // ...
    $file = $this->pdfFileGenerator->template('test.html.twig', 'test.pdf', ['name' => 'John']);
  ```
* `url(string $url, string $output, PdfFileGeneratorOptions|array $options = PdfFileGeneratorOptions::DEFAULT): SplFileInfo`:
  Generate a PDF file from url.

  Example:
  ```php
  <?php
    // ...
    $file = $this->pdfFileGenerator->url('https://www.digitalgarden.fr', 'output.pdf');
  ```

These helpers use `PdfFileGeneratorOptions` which are : 
*  `OPTION_ASYNC` *(bool) (default **false**)* : If true, enable the asynchronous generation
   (see **Asynchronous generation** chapter.).

Commands
--------

Here the list of commands added by the bundle.

```shell
$ bin/console dgarden:pdf:html --help
Description:
  Generates a pdf from an HTML or an HTML file.

Usage:
  dgarden:pdf:html [options] [--] <html> <output_file>
  dgarden:gotenberg:html
  dgarden:gotenberg:generate-html
  dgarden:pdf:generate-html

Arguments:
  html                  The HTML or HTML file to convert to pdf.
  output_file           The output file.

Options:
      --async           Generate the file asynchronously.
  -h, --help            Display help for the given command. When no command is given display help for the list command
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -e, --env=ENV         The Environment name. [default: "dev"]
      --no-debug        Switch off debug mode.
      --profile         Enables profiling (requires debug).
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  This command allows you to generate a pdf from an HTML or an HTML file.
```

```shell
$ bin/console dgarden:pdf:merge --help
Description:
  Generates a pdf from an HTML or an HTML file.

Usage:
  dgarden:pdf:merge [options] [--] <files>...
  dgarden:gotenberg:merge
  dgarden:gotenberg:merge-pdf
  dgarden:pdf:merge-pdf

Arguments:
  files                 The files to merge. The last file given is used as the output file.

Options:
      --async           Generate the file asynchronously.
  -h, --help            Display help for the given command. When no command is given display help for the list command
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -e, --env=ENV         The Environment name. [default: "dev"]
      --no-debug        Switch off debug mode.
      --profile         Enables profiling (requires debug).
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  This command allows you to generate a pdf from an HTML or an HTML file.
```

```shell
$ bin/console dgarden:pdf:template --help
Description:
  Generate a pdf from a template.

Usage:
  dgarden:pdf:template [options] [--] <template> <output_file>
  dgarden:gotenberg:template
  dgarden:gotenberg:generate-template
  dgarden:pdf:generate-template

Arguments:
  template                         The template to convert to pdf.
  output_file                      The output file.

Options:
  -c, --context=CONTEXT            The context to use for the template. (multiple values allowed)
  -j, --json-context=JSON-CONTEXT  The JSON encoded context to use for the template. (multiple values allowed)
      --async                      Generate the file asynchronously.
  -h, --help                       Display help for the given command. When no command is given display help for the list command
  -q, --quiet                      Do not output any message
  -V, --version                    Display this application version
      --ansi|--no-ansi             Force (or disable --no-ansi) ANSI output
  -n, --no-interaction             Do not ask any interactive question
  -e, --env=ENV                    The Environment name. [default: "dev"]
      --no-debug                   Switch off debug mode.
      --profile                    Enables profiling (requires debug).
  -v|vv|vvv, --verbose             Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  This command allows you to generate a PDF file from a template.
```

```shell
$ bin/console dgarden:pdf:url --help     
Description:
  Generates a pdf from a url.

Usage:
  dgarden:pdf:url [options] [--] <url> <output_file>
  dgarden:gotenberg:url
  dgarden:gotenberg:generate-url
  dgarden:pdf:generate-url

Arguments:
  url                   The url to convert to pdf.
  output_file           The output file.

Options:
      --async           Generate the file asynchronously.
  -h, --help            Display help for the given command. When no command is given display help for the list command
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -e, --env=ENV         The Environment name. [default: "dev"]
      --no-debug        Switch off debug mode.
      --profile         Enables profiling (requires debug).
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  This command allows you to generate a pdf from a url.
```

Asynchronous generation
-----------------------

Gotenberg includes the possibility to generate pdf asynchronously, meaning that you ask it to
generate your pdf then send a webhook to your application when it finishes.

DigitalGardenGotenbergBundle includes, if your project has controllers dependency and a router,
a route Gotenberg can access to :

```shell
$ bin/console debug:router dgarden_gotenberg_async_pdf_generation
+--------------+--------------------------------------------------------------+
| Property     | Value                                                        |
+--------------+--------------------------------------------------------------+
| Route Name   | dgarden_gotenberg_async_pdf_generation                       |
| Path         | /_dg/pdf/generate                                            |
| Path Regex   | {^/_dg/pdf/generate$}sDu                                     |
| Host         | ANY                                                          |
| Host Regex   |                                                              |
| Scheme       | ANY                                                          |
| Method       | POST                                                         |
| Requirements | NO CUSTOM                                                    |
| Class        | Symfony\Component\Routing\Route                              |
| Defaults     | _controller: dgarden.gotenberg.action.async_pdf_generation() |
| Options      | compiler_class: Symfony\Component\Routing\RouteCompiler      |
|              | utf8: true                                                   |
+--------------+--------------------------------------------------------------+
```

So, everytime you make an asynchronous request, `PdfFileGenerator` service will get this route
url from your router and send it to Gotenberg.

Contribution
============

If you want to contribute, please follow these rules :

* **Respect Gitflow**: Respect following branches : 
  * **develop**: Contains the last version of the bundle code.
  * **master**: Contains the production version of the bundle code.
  * **feature/***: Branches adding new features to the bundle. They have to be merged on **develop**, and will be
    merge to master with the next release.
  * **bugfix/***: Branches fixing non-blocking bugs. They have to be merged on **develop**, and will be
    merge to master with the next release. 
  * **hotfix/***: Branches fixing blocking bugs. They have to be merged on **develop**, **master** and every opened
    **releases**. After merging them to master, create a new **patch version**. (ex: v0.2.0 -> v0.2.1)
  * **release/***: Branches containing future releases. They can be edited and will be merged, when finished, to
    **develop** and **master**. After merged to master, create a new **minor version** (ex: v0.2.1 -> v0.3.0) or major
    version (ex: v0.2.1 -> v1.0.0). To know if you need to increase the major version, ask yourself : 
    * Does my release add a game-changing functionality.
    * Does my release breaks the retro-compatibility of the bundle.
    * Does that release will have to evolve in parallel to the current version.
* **Launch and edit tests**: If you run `phpunit` on this bundle, you'll have coverage generated in
  `.phpunit-cache/code-coverage` directory, take a look.
* **Edit the CHANGLOG.md** file with your changes.
* **Edit the TODO.md** file with your ideas if any.