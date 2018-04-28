<?php
namespace AppBundle\Console\Commands\Makers\Util;


use AppBundle\Console\Commands\Makers\Util\TemplateMaker;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Input\InputArgument;

class PatternMaker
{

    private $pathToApp;


    public function __construct($pathToApp)
    {
        $this->pathToApp = $pathToApp;
    }


    public function MakePatternByName(string $name, string $modelName)
    {

        $templateMaker = new TemplateMaker($name, $modelName, "AppBundle");

        $interfaceFileName = $templateMaker->getInterfaceFileName();
        $classFileName = $templateMaker->getClassFileName();
        $classTemplate = $templateMaker->getClassTemplate();
        $interfaceTemplate = $templateMaker->getInterfaceTemplate();

        $pathToClassfile = $this->pathToApp . $name . "/Impl/" . $classFileName;
        $pathToInterfacefile = $this->pathToApp . $name . "/" . $interfaceFileName;

        if (!file_exists($this->pathToApp . $name)) {
            mkdir($this->pathToApp . $name);
        }
        if (!file_exists($this->pathToApp . $name . '/Impl')) {
            mkdir($this->pathToApp . $name . '/Impl');
        }

        if (!file_exists($pathToInterfacefile)) {

            $fp = fopen($pathToInterfacefile, "w");
            fwrite($fp, $interfaceTemplate);
            fclose($fp);

            if (!file_exists($pathToClassfile)) {

                $fp = fopen($pathToClassfile, "w");
                fwrite($fp, $classTemplate);
                fclose($fp);

            } else {
                return [
                    "message" => "Реализация данного паттерна уже существует",
                    "type" => "error"
                ];
            }

        } else {
            return [
                "message" => "Интерфес данного паттерна уже существует",
                "type" => "error"
            ];
        }


        $application = new Application(new KernelInterface());
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
           'command' => 'make:provider',
           // (optional) define the value of command arguments
           'name' => $modelName . "Provider"
        ));

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);

        // return the output, don't use if you used NullOutput()
        $content = $output->fetch();

        return [
            "message" => "Паттерн создан",
            "type" => "info"
        ];

    }
}