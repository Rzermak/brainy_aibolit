<script src="{$template_path}js/aibolit.js"></script>
<link rel="stylesheet" type="text/css" href="{$template_path}css/aibolit.css" />
<h2>Aibolit</h2>

{if $error_crontab}
    <div class="status">
        <p>
            <span class="aibolit_license">Не удалось автоматически установить запись в планировщик, вам необходимо это сделать вручную: <b>{$error_crontab}</b></span>
        </p><br>
        <p>
            Команда:<br><br><b>{$crontab_command.minutes} {$crontab_command.hours} {$crontab_command.days} {$crontab_command.months} {$crontab_command.weekdays} {$crontab_command.command} > {$crontab_command.output}</b>
        </p><br>
        <p>
            Вы можете устанавливать любое значения для времени запуска и вывода информации. Рекомендуется запускать скрипт каждую минуту.<br>
            Запуск должен быть от того пользователя, которому разрешён доступ к проверяемым сайтам и файлам модуля. По умолчанию устанавливается запуск от имени пользователя root
        </p>
        <p>
            <br><a href="/index.php?do=crontab&cron_user=root" class="btn btn-wrench">Управление планировщиком</a>
        </p>
    </div>
{else}
    {if !$components.php}
    <div class="status">
        <p>
            <span class="aibolit_license">Внимание! Для работы компонентов модуля необходима <b>php70</b>.<br>Для продолжения перейдите в управление сервером и установите указанную версию.</span>
        </p>
        <p>
            <br><a href="/index.php?do=server_control&p=1" class="btn btn-wrench" target="_blank">Управление сервером</a>
        </p>
    </div>
    {/if}
    {if !$components.cron}
    <div class="wrap">
        <h3>Установка</h3>
        <div class="div-20">
            <p>
                <span class="aibolit_license">
                    Внимание! Данный модуль основывается на работе антивируса <a href="https://revisium.com/ai/" target="_blank">Ai-Bolit</a>.<br>
                    <b class="aibolit_license">Автор не несёт ответственности за приченённые неудобства или потери данных. Вы используете модуль на свой страх и риск.</b>
                </span>
            </p><br><br>
            <a href="/index.php?do=aibolit&subdo=install" class="btn btn-wrench">Установить модуль</a>
        </div>
    </div>
    {/if}
{/if}