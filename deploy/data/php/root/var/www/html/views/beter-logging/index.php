<?php

/** @var yii\web\View $this */

$this->title = 'My Yii Application';
?>
<div class="site-index">
    <div class="body-content">

        <div class="row">
            <div class="col-lg-12">
                <h2>Default log targets.</h2>

                <p>
                    By default yii test app uses file log. It can be found in /var/www/html/runtime/logs folder.
                    In this test no stderr of logstash output will be generated.
                </p>

                <p>
                    Expected result: no stderr, no logstash.
                </p>

                <p><a class="btn btn-outline-secondary" href="<?php echo \yii\helpers\Url::to(['beter-logging/default-log-targets']) ?>">Test it</a></p>
            </div>
            <div class="col-lg-12">
                <h2>Log Level: Log Target and Handler</h2>
                <p>
                    The very first filter is "ProxyLogTarget". So, if it skips log entry no log entries will be passed
                    to handlers. Different Log Targets may be configured with different levels. In this example
                    "ProxyLogTarget" allows only "error" and "warning" level, but "standard_stream" handler allows
                    "debug" level.
                </p>

                <p>
                    <b>
                        Don't forget that Yii level setting must be an array of levels. Monolog level setting is a
                        minimum level that handler handles.
                    </b>
                </p>

                <p>
                    Expected result: No messages are generated, because "ProxyLogTarget" skips them.
                </p>

                <p><a class="btn btn-outline-secondary" href="<?php echo \yii\helpers\Url::to(['beter-logging/log-target-and-handler-level']) ?>">Test it</a></p>
            </div>
            <div class="col-lg-12">
                <h2>Colorized Standard Stream handler</h2>

                <p>Just demonstration of pretty printing for the dev environment.</p>

                <p><a class="btn btn-outline-secondary" href="<?php echo \yii\helpers\Url::to(['beter-logging/colorized-standard-stream']) ?>">Test it</a></p>
            </div>
            <div class="col-lg-12">
                <h2>No pretty printing for Standard Stream handler</h2>

                <p>Just demonstration of disabled pretty printing for the dev environment.</p>

                <p><a class="btn btn-outline-secondary" href="<?php echo \yii\helpers\Url::to(['beter-logging/no-pretty-printing-standard-stream']) ?>">Test it</a></p>
            </div>
            <div class="col-lg-12">
                <h2>Standard Stream handler for production environments</h2>

                <p>For production environments better to use json formatter, or logstash formatter.</p>

                <p><a class="btn btn-outline-secondary" href="<?php echo \yii\helpers\Url::to(['beter-logging/production-standard-stream']) ?>">Test it</a></p>
            </div>
            <div class="col-lg-12">
                <h2>Bubbling test 1</h2>

                <p>2 standard stream handlers with disabled bubbling.<br />
                    Expected result: Only one info message must be displayed if no errors have occurred in the first handler.</p>
                <p><a class="btn btn-outline-secondary" href="<?php echo \yii\helpers\Url::to(['beter-logging/bubbling1']) ?>">Test it</a></p>
            </div>
            <div class="col-lg-12">
                <h2>Bubbling test 2</h2>

                <p>2 standard stream handlers with enabled bubbling.<br />
                    Expected result: Two similar info messages must be displayed whenever errors have occurred in the first handler.</p>
                <p><a class="btn btn-outline-secondary" href="<?php echo \yii\helpers\Url::to(['beter-logging/bubbling2']) ?>">Test it</a></p>
            </div>
            <div class="col-lg-12">
                <h2>Bubbling test 3</h2>

                <p>2 standard stream handlers with enabled bubbling.<br />
                    One of them is configured to handle "warning" level, another is configured to handle "info" level.
                </p>
                <p>
                    Expected result: Two similar warning messages, one info messages must be displayed whenever errors have occurred in the first handler.</p>
                <p><a class="btn btn-outline-secondary" href="<?php echo \yii\helpers\Url::to(['beter-logging/bubbling3']) ?>">Test it</a></p>
            </div>
            <div class="col-lg-12">
                <h2>Bubbling test 4</h2>

                <p>logstash handler and standard stream handlers with disabled bubbling.<br />
                    logstash handler configured to connect to invalid host:port and this causes failure to deliver log
                    entry to logstash. So, bubbling will be forced to activate, despite it wa disabled.<br />
                    <br /><b>ORDER OF HANDLERS IS IMPORTANT!</b><br />
                    Expected result: One info message handled by standard handler, plus a few messages with info about failure.</p>
                <p><a class="btn btn-outline-secondary" href="<?php echo \yii\helpers\Url::to(['beter-logging/bubbling4']) ?>">Test it</a></p>
            </div>
        </div>

    </div>
</div>
