<?php

namespace App\Command;

use App\Machine\CandyCatalogInterface;
use App\Machine\MachineInterface;
use App\Machine\PurchaseTransaction;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

final class PurchaseCandyCommand extends Command
{
    private MachineInterface $machine;
    private CandyCatalogInterface $catalog;

    const NAME_SEPARATOR = ' - ';

    public function __construct(MachineInterface $machine, CandyCatalogInterface $catalog)
    {
        $this->machine = $machine;
        $this->catalog = $catalog;
        
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('purchase-candy')
            ->setDescription('Purchase candy from the candy machine')
            ->setHelp('This command allows you to purchase candy from the candy machine');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        $io->title('Welcome to the Candy Machine!');

        try {
            // Get candy type
            $candyType = $this->getCandyType($helper, $input, $output, $io);
            
            // Get quantity
            $quantity = $this->getQuantity($helper, $input, $output, $io);
            
            // Get payment amount
            $paymentAmount = $this->getPaymentAmount($helper, $input, $output, $io);

            // Process transaction
            $transaction = new PurchaseTransaction($candyType, $quantity, $paymentAmount);
            $result = $this->machine->execute($transaction);

            // Display result
            $this->displayResult($result, $io);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
    }

    private function getCandyType($helper, InputInterface $input, OutputInterface $output, SymfonyStyle $io): string
    {
        $candyNames = $this->catalog->getCandyNames();
        $choices = [];
        
        // Using string keys instead of indices for ChoiceQuestion
        foreach ($candyNames as $key => $name) {
            $price = $this->catalog->getCandyPrice($name);
            $choices[$key] = "{$name} " . self::NAME_SEPARATOR . " {$price}€";
        }

        $question = new ChoiceQuestion(
            'Please select your favorite candy:',
            $choices
        );
        $question->setErrorMessage('Candy selection %s is invalid.');

        $selectedCandy = $helper->ask($input, $output, $question);

        // Prepare selected candy for validation
        $selectedCandy = explode(self::NAME_SEPARATOR, $selectedCandy);
        $selectedCandy = trim($selectedCandy[0]);

        if (!$this->catalog->isValidCandyType($selectedCandy)) {
            throw new \InvalidArgumentException('Invalid candy selection');
        }

        $io->writeln(sprintf('You selected: %s', $selectedCandy));

        return $selectedCandy;
    }

    private function getQuantity($helper, InputInterface $input, OutputInterface $output, SymfonyStyle $io): int
    {
        $question = new Question('Please input packs of candy you want to buy (Default: 1): ', 1);
        $question->setValidator(function ($answer) {
            $quantity = (int) $answer;
            if ($quantity <= 0) {
                throw new \InvalidArgumentException('Quantity must be greater than 0');
            }
            return $quantity;
        });

        return (int) $helper->ask($input, $output, $question);
    }

    private function getPaymentAmount($helper, InputInterface $input, OutputInterface $output, SymfonyStyle $io): float
    {
        $question = new Question('Please input your payment amount: ');
        $question->setValidator(function ($answer) {
            $amount = (float) $answer;
            if ($amount < 0) {
                throw new \InvalidArgumentException('Payment amount cannot be negative');
            }
            return $amount;
        });

        return (float) $helper->ask($input, $output, $question);
    }

    private function displayResult($result, SymfonyStyle $io): void
    {
        $io->newLine();

        if ($result->getItemQuantity() > 1) {
            $io->success(sprintf(
                'You bought %d packs of %s for %.2f€, each for %.2f€',
                $result->getItemQuantity(),
                $result->getType(),
                $result->getTotalAmount(),
                $result->getUnitPrice()
            ));
        } else {
            $io->success(sprintf(
                'You bought %d pack of %s for %.2f€',
                $result->getItemQuantity(),
                $result->getType(),
                $result->getTotalAmount()
            ));
        }


        $change = $result->getChange();
        if (empty($change)) {
            $io->info('No change needed - exact payment!');
            return;
        }

        $io->writeln('Your change is:');
        
        $table = new Table($io);
        $table->setHeaders(['Coin', 'Count']);
        
        foreach ($change as $coin => $count) {
            $table->addRow([$coin . '€', $count]);
        }
        
        $table->render();
    }
}