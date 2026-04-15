<?php
	namespace App\Http\Controllers;
use Log;
use Exception;
use Aws\CommandPool;
use Aws\Ses\SesClient;
use GuzzleHttp\Client;
use Aws\ResultInterface;
use Aws\CommandInterface;
use Illuminate\Http\Request;
use Aws\Exception\AwsException;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Exception\GuzzleException;
class MailController extends Controller
{
 public function test()
  {
    $client = new SesClient([
      'version' => 'latest',
      'region' => 'eu-west-1',
      'credentials' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
      ],
    ]);
      $recipients = [
     
       'info@randevumcepte.com.tr',
        
      ];
     // Shuffle recipients for testing purposes
    shuffle($recipients);
    // Queue emails as SendEmail commands
      $i = 100;
    $commands = [];
    foreach ($recipients as $recipient) {
        $commands[] = $client->getCommand('SendEmail', [
         // Pass the message id so it can be updated after it is processed (it's ignored by SES)
          'x-message-id' => $i,
          'Source'   => 'randevumcepte.com.tr <bildirim@randevumcepte.com.tr>',
         'Destination' => [
            'ToAddresses' => [$recipient],
        ],
        'Message'   => [
           'Subject' => [
             'Data'  => 'SES API test',
              'Charset' => 'UTF-8',
           ],
           'Body'  => [
            'Html' => [
                'Data'  => 'This is a <b>test</b>.',
                'Charset' => 'UTF-8',
              ],
            ],
         ],
      ]);
       $i++;
      }
      try
    {
        $timeStart = microtime(true);
        $pool = new CommandPool($client, $commands, [
         'concurrency' => 10,
        'before'   => function (CommandInterface $cmd, $iteratorId) {
           $a = $cmd->toArray();
          // echo sprintf('About to send %d: %s' . PHP_EOL, $iteratorId, $a['Destination']['ToAddresses'][0]);
            echo sprintf('<br>About to send ' .$iteratorId .': '. $a['Destination']['ToAddresses'][0]);
        },
        'fulfilled' => function (ResultInterface $result, $iteratorId) use ($commands) {
          // echo sprintf(
            //  'Completed %d: %s' . PHP_EOL,
          //  $commands[$iteratorId]['x-message-id'],
            //  $commands[$iteratorId]['Destination']['ToAddresses'][0]
           // );
          echo sprintf('<br>Completed ' .$commands[$iteratorId]['x-message-id'].' :'.$commands[$iteratorId]['Destination']['ToAddresses'][0]);
        },
        'rejected'  => function (AwsException $reason, $iteratorId) use ($commands) {
          // echo sprintf(
            //  'Failed %d: %s' . PHP_EOL,
          //  $commands[$iteratorId]['x-message-id'],
            //  $commands[$iteratorId]['Destination']['ToAddresses'][0]
           // );
          
           echo sprintf('<br>Reason : '.$reason);
           echo sprintf('<br>Amazon SES Failed Rejected:' . $commands[$iteratorId]['x-message-id'] . ' :' . $commands[$iteratorId]['Destination']['ToAddresses'][0]);
          },
      ]);
       // Initiate the pool transfers
       $promise = $pool->promise();
      // Force the pool to complete synchronously
      $promise->wait();
        $timeEnd = microtime(true);

        echo sprintf('<br>Operation completed in %s seconds' . PHP_EOL, $timeEnd - $timeStart);
      } catch (Exception $e) {
      // echo sprintf('Error: %s' . PHP_EOL, $e->getMessage());
      echo('<br>Catch Block: Amazon SES Exception : ' . $e->getMessage());
      }
  }
}