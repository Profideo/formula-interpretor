<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Change visibility of an PHPExcel_Calculation variable visibility to be able to create custom functions
 */
class ChangePhpExcelAttributeVisibilityCommand extends ContainerAwareCommand
{
    /**
     * @var OutputInterface $output
     */
    private $output;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('excelformulas:changePhpExcelAttributeVisibility')
            ->setDescription('Change visibility of an PHPExcel_Calculation variable visibility to be able to create custom functions');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var OutputInterface $output */
        $this->output = $output;

        $container = $this->getContainer();

        try {
            $file = $this->getContainer()->get('kernel')->getRootDir() . "/../vendor/phpoffice/phpexcel/Classes/PHPExcel/Calculation.php";
            $fileContent = file_get_contents($file);
            if(empty($fileContent)) {
                throw new \Exception(sprintf("The file %s is empty", $file));
            }

            $fileContentReplaced = str_replace('private static $_PHPExcelFunctions', 'protected static $_PHPExcelFunctions', $fileContent);
            file_put_contents($file, $fileContentReplaced);
        } catch(\Exception $e) {
            $this->showMessage(">> Failed changing variable visibility", 'red');
            $this->showMessage(">> " . $e->getMessage(), 'red');
        }

        $this->showMessage(">> Successfully change variable visibility", 'green');

        return 0;
    }

    /**
     * The following function writes logs inside a log file or|and in the console.
     *
     * @param  string $message
     * @param  string  $color
     * @param  boolean $inLog
     * @param  boolean $inOutput
     */
    private function showMessage($message, $color = 'white'){
        $this->output->writeln("<fg=$color>$message</fg=$color>");
    }
}
