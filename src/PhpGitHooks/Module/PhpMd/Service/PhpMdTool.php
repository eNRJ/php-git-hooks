<?php

namespace PhpGitHooks\Module\PhpMd\Service;

use PhpGitHooks\Module\Git\Contract\Response\BadJobLogoResponse;
use PhpGitHooks\Module\Git\Service\PreCommitOutputWriter;
use PhpGitHooks\Module\PhpMd\Contract\Exception\PhpMdViolationsException;
use PhpGitHooks\Module\PhpMd\Model\PhpMdToolProcessorInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PhpMdTool
{
    const CHECKING_MESSAGE = 'Checking code mess with PHPMD';
    /**
     * @var OutputInterface
     */
    private $output;
    /**
     * @var PhpMdToolProcessorInterface
     */
    private $phpMdToolProcessor;

    /**
     * PhpMdTool constructor.
     *
     * @param OutputInterface             $output
     * @param PhpMdToolProcessorInterface $phpMdToolProcessor
     */
    public function __construct(OutputInterface $output, PhpMdToolProcessorInterface $phpMdToolProcessor)
    {
        $this->output = $output;
        $this->phpMdToolProcessor = $phpMdToolProcessor;
    }

    /**
     * @param array  $files
     * @param string $options
     * @param string $errorMessage
     *
     * @throws PhpMdViolationsException
     */
    public function execute(array  $files, $options, $errorMessage)
    {
        $outputMessage = new PreCommitOutputWriter(self::CHECKING_MESSAGE);
        $this->output->write($outputMessage->getMessage());

        $errors = [];
        foreach ($files as $file) {
            $errors[] = $this->phpMdToolProcessor->process($file, $options);
        }

        $errors = array_filter($errors);

        if (!empty($errors)) {
            $outputText = $outputMessage->setError(implode('', $errors));
            $this->output->writeln($outputMessage->getFailMessage());
            $this->output->writeln($outputText);
            $this->output->writeln(BadJobLogoResponse::paint($errorMessage));
            throw new PhpMdViolationsException();
        }

        $this->output->writeln($outputMessage->getSuccessfulMessage());
    }
}
