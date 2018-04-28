<?php

namespace AppBundle\Console\Commands\Makers\Util;


class TemplateMaker
{

    private $modelName;
    private $patternName;
    private $nameSpace;
    private $interfaceTemplatePath = "src/AppBundle/Console/Commands/Makers/Util/templates/interface.txt";
    private $classTemplatePath = "src/AppBundle/Console/Commands/Makers/Util/templates/class.txt";

    public function __construct(string $patternName, string $modelName, string $nameSpace = "")
    {

        if (empty(trim($modelName))) {
            throw new \Exception("");
        }
        $this->modelName = $modelName;

        if (empty(trim($patternName))) {
            throw new \Exception("");
        }
        $this->patternName = $patternName;

        if (!empty(trim($nameSpace)) && substr($nameSpace, -1) == '\\') {
            $nameSpace = substr($nameSpace, 0, strlen($nameSpace) - 1);
        }
        $this->nameSpace = $nameSpace;
    }

    private function getTemplate(string $templatePath):string
    {
        $template = file_get_contents($templatePath);
        $template = str_replace('{namespace}', $this->nameSpace, $template);
        $template = str_replace('{modelName}', $this->modelName, $template);
        $template = str_replace('{patternName}', $this->patternName, $template);
        return $template;
    }

    public function getInterfaceTemplate()
    {
        return $this->getTemplate($this->interfaceTemplatePath);
    }

    public function getClassTemplate()
    {
        return $this->getTemplate($this->classTemplatePath);
    }

    public function getInterfaceFileName():string
    {
        return $this->modelName . $this->patternName . ".php";
    }

    public function getClassFileName():string
    {
        return $this->modelName . $this->patternName . "Impl.php";
    }
}



