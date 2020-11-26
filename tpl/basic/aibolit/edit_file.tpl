<form name="editFile" method="post" action="index.php?do=aibolit">
    <input type="hidden" name="subdo" value="save_file">
    <input type="hidden" name="file" value="{$file}">
    <input type="hidden" name="site" value="{$site}">
    <div class="list-div">
        <label>Файл: {$file}</label>
        <textarea name="file_content" class="aibolit_textarea_file">{$file_content}</textarea>
    </div>
    <div><button type="submit" class="btn btn-check">Сохранить</button> <span data-update-result="true"></span></div>
</form>