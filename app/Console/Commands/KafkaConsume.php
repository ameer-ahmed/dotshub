<?php

namespace App\Console\Commands;

use App\Http\Services\V1\Abstracts\System\Questionnaire\QuestionnaireAbstractService;
use Exception;
use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Contracts\ConsumerMessage;

class KafkaConsume extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:questionnaires-consumer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume questionnaires messages from Kafka';

    public function __construct(
        private readonly QuestionnaireAbstractService $questionnaireAbstractService,
    )
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Kafka consumer for gateway questionnaires...');
        $this->info('Consumer Group: gateway-questionnaires-consumer');
        $this->info('Waiting for messages...');
        $this->newLine();

        try {
            $consumer = Kafka::consumer(['gateway.questionnaires'])
                ->withBrokers('kafka:9092')
                ->withConsumerGroupId('gateway-questionnaires-consumer')
                ->withAutoCommit()
                ->withHandler(function (ConsumerMessage $message) {
                    // Get the message body
                    $data = $message->getBody();

                    // Get headers (optional)
                    $headers = $message->getHeaders();

                    // Process the message
                    $this->info('✓ Message received!');

                    $this->info(print_r($data, true));

                    tenancy()->find($headers['tenant'])->run(function () use ($data) {
                        $this->questionnaireAbstractService->destroy($data['data']['id']);
                    });

                    return true;
                })
                ->withOptions([
                    'auto.offset.reset' => 'earliest', // Start from beginning if no offset
                    'enable.auto.commit' => 'true',
                ])
                ->build();

            $consumer->consume();

        } catch (\Exception $e) {
            $this->error('Kafka consumer error: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
