<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2021. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */

namespace App\Listeners\Quote;

use App\Jobs\Mail\EntitySentMailer;
use App\Libraries\MultiDB;
use App\Notifications\Admin\EntitySentNotification;
use App\Utils\Traits\Notifications\UserNotifies;
use Illuminate\Contracts\Queue\ShouldQueue;

class QuoteEmailedNotification implements ShouldQueue
{
    use UserNotifies;

    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        MultiDB::setDb($event->company->db);

        $first_notification_sent = true;

        $quote = $event->invitation->quote;
        $quote->last_sent_date = now();
        $quote->save();

        foreach ($event->invitation->company->company_users as $company_user) {
            $user = $company_user->user;

            $notification = new EntitySentNotification($event->invitation, 'quote');

            $methods = $this->findUserNotificationTypes($event->invitation, $company_user, 'quote', ['all_notifications', 'quote_sent']);

            if (($key = array_search('mail', $methods)) !== false && $first_notification_sent === true) {
                unset($methods[$key]);

                EntitySentMailer::dispatch($event->invitation, 'quote', $user, $event->invitation->company, $event->template);
                $first_notification_sent = false;
            }

            $notification->method = $methods;

            $user->notify($notification);
        }
    }
}