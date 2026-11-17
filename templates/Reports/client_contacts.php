<?php
/**
 * Client contact list report.
 *
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Client> $clients
 * @var array|null $currentUser
 */
?>
<h3>Client Contact Report</h3>

<p><span class="bold">Report Generated On: </span><?= date('Y-m-d') ?> </p>

<p><span class="bold">By: </span><?= h(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')) ?> </p>


<?php foreach ($clients as $client) : ?>

    <div class="contact_card">

        <h5><?= h($client->client_name) ?></h5>

        <div class="container">

            <div class="two columns">

                <!-- CLIENT DETAILS -->

                <p><span class="bold">Contact Name: </span><?= h($client->client_contact) ?></p>

                <p><span class="bold">Phone: </span> <?= h($client->client_phone) ?></p>

                <p><span class="bold">Email: </span> <?= h($client->client_email) ?></p>

                <!-- // END CLIENT DETAILS -->

            </div>

            <div class="two columns">

                <!-- CLIENT ADDRESS -->

                <span><?= h($client->client_name) ?></span><br />
                <?php
                foreach (['client_address_line_1', 'client_address_line_2', 'client_town', 'client_city', 'client_state'] as $field) {
                    if ($client->get($field)) {
                        echo h($client->get($field)) . '<br />';
                    }
                }
                if ($client->client_zip) {
                    echo h($client->client_zip) . '<br /><br />';
                }
                ?>

                <!-- // END CLIENT ADDRESS -->

            </div>

        </div>

        <!-- // END CONTAINER -->

    </div>

<?php endforeach; ?>


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?= $this->Html->div('div_link info_button_normal', '<span class="icon-arrow-left "></span><a href="javascript:history.back()">Back</a>') ?>
<?php $this->end(); ?>

<!-- // END INFO TILE -->
