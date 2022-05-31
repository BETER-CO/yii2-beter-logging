<?php

/** @var yii\web\View $this */

$this->title = 'My Yii Application';
?>
<div class="site-index">
    <div class="body-content">

        <div class="row">
            <h2><?= $actionName; ?></h2>
            <div class="col-lg-12">
<?php
            foreach ($data as $key => $value) {
                $prettyValue = var_export($value, true);
                echo "<hr />\n<p>$key</p><pre>$prettyValue</pre>";
            }
?>
            </div>
        </div>

    </div>
</div>
