<?php

namespace OCA\ImageSigning\templates;

use OCA\ImageSigning\lib\php\settings;

settings::init();
$context = settings::getAll();


script('imagesigning', [
    's7a-dialogs',
    'admin'
]);

style('imagesigning', [
	'admin'
])

///** @var \OCP\IL10N $l */
?>
<form id="ImageSigning" class="section">

    <h2>ImageSigning</h2>

    <div class="field">
        <select>
            <?php
                foreach ($context['signers'] as $a) {
                    echo('<option value="'.$a['id'].'">'.$a['name'].'</option>');
                }
            ?>
        </select>
        <button id="imagesigning_add" type="submit"><?php p($l->t('+')); ?></button>
    </div>

    <div class="field">
        <input id="imagesigning_name" type="text" placeholder="Name" required />
        <input id="imagesigning_worker" type="text" placeholder="Worker Id" required />
        <input id="imagesigning_active" type="checkbox">
        <label for="imagesigning_active">Active</label>
    </div>

    <div class="field">
        <input id="imagesigning_url" type="text" placeholder="URL" required />
    </div>

    <div class="field">
        <button id="imagesigning_remove" type="submit"><?php p($l->t('Remove')); ?></button>
        <button id="imagesigning_update" type="submit"><?php p($l->t('Update')); ?></button>
    </div>

</form>
