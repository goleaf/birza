<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Laravel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
        integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"
        integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous">
    </script>
    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css"
        rel="stylesheet" />
    <script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js">
    </script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.js"></script>

    <style>
        a.status-1 {
            font-weight: bold;
        }
    </style>
    <script>
        jQuery(document).ready(function($) {

            $.ajaxSetup({
                beforeSend: function(xhr, settings) {
                    console.log('beforesend');
                    settings.data += "&_token=<?php echo csrf_token(); ?>";
                }
            });

            $('.editable').editable().on('hidden', function(e, reason) {
                var locale = $(this).data('locale');
                if (reason === 'save') {
                    $(this).removeClass('status-0').addClass('status-1');
                }
                if (reason === 'save' || reason === 'nochange') {
                    var $next = $(this).closest('tr').next().find('.editable.locale-' + locale);
                    setTimeout(function() {
                        $next.editable('show');
                    }, 300);
                }
            });

            $('.group-select').on('change', function() {
                var group = $(this).val();
                if (group) {
                    window.location.href = '<?php echo action('\Barryvdh\TranslationManager\Controller@getView'); ?>/' + $(this).val();
                } else {
                    window.location.href = '<?php echo action('\Barryvdh\TranslationManager\Controller@getIndex'); ?>';
                }
            });

            $("a.delete-key").on('confirm:complete', function(event, result) {
                if (result) {
                    var row = $(this).closest('tr');
                    var url = $(this).attr('href');
                    var id = row.attr('id');
                    $.post(url, {
                        id: id
                    }, function() {
                        row.remove();
                    });
                }
                return false;
            });

            $('.form-import').on('ajax:success', function(e, data) {
                $('div.success-import strong.counter').text(data.counter);
                $('div.success-import').slideDown();
                window.location.reload();
            });

            $('.form-find').on('ajax:success', function(e, data) {
                $('div.success-find strong.counter').text(data.counter);
                $('div.success-find').slideDown();
                window.location.reload();
            });

            $('.form-publish').on('ajax:success', function(e, data) {
                $('div.success-publish').slideDown();
            });

            $('.form-publish-all').on('ajax:success', function(e, data) {
                $('div.success-publish-all').slideDown();
            });
            $('.enable-auto-translate-group').click(function(event) {
                event.preventDefault();
                $('.autotranslate-block-group').removeClass('hidden');
                $('.enable-auto-translate-group').addClass('hidden');
            })
            $('#base-locale').change(function(event) {
                console.log($(this).val());
                $.cookie('base_locale', $(this).val());
            })
            if (typeof $.cookie('base_locale') !== 'undefined') {
                $('#base-locale').val($.cookie('base_locale'));
            }

            // Handle auto-translation of keys
            $('.auto-translate-key').on('click', function(event) {
                event.preventDefault();

                const $button = $(this);
                const $row = $button.closest('tr');
                const key = $row.attr('id');
                const sourceText = $row.find('td:first').text().trim();

                // Disable button while translating
                $button.prop('disabled', true);

                // Get all editable cells in this row
                const translationPromises = [];

                $row.find('.editable').each(function() {
                    const $target = $(this);
                    const targetLanguage = $target.data('locale');

                    // Create promise for translation request
                    const promise = new Promise((resolve, reject) => {
                        const proxyUrl = 'https://api.allorigins.win/get?url=' +
                            encodeURIComponent(
                                'https://655.mtis.workers.dev/translate' +
                                '?text=' + encodeURIComponent(sourceText.replace(/_/g,
                                    ' ')) +
                                '&source_lang=en' +
                                '&target_lang=' + targetLanguage
                            );

                        $.ajax({
                                method: 'GET',
                                url: proxyUrl,
                                crossDomain: true
                            })
                            .done(response => {
                                try {
                                    const data = JSON.parse(response.contents);
                                    if (data && data.response && data.response
                                        .translated_text) {
                                        const translatedText = data.response
                                            .translated_text;

                                        // Send AJAX request to save the translation
                                        $.ajax({
                                            method: 'POST',
                                            url: $target.data('url'),
                                            data: {
                                                name: $target.data('name'),
                                                value: translatedText,
                                                pk: $target.data('pk')
                                            },
                                            success: function() {
                                                $target.editable('setValue',
                                                    translatedText);
                                                resolve();
                                            },
                                            error: function() {
                                                reject(
                                                    'Failed to save translation'
                                                    );
                                            }
                                        });
                                    } else {
                                        reject('Invalid response structure');
                                    }
                                } catch (e) {
                                    reject('Failed to parse response');
                                }
                            })
                            .fail(reject);
                    });

                    translationPromises.push(promise);
                });

                // Re-enable button when all translations complete
                Promise.allSettled(translationPromises).finally(() => {
                    $button.prop('disabled', false);
                });
            });

        })
    </script>
</head>

<body>
    <div class="container-fluid">
        <div class="alert alert-success success-import" style="display:none;">
            <p><?php echo __('translation.done_importing', ['count' => '<strong class="counter">N</strong>']); ?> <?php echo __('translation.reload_page'); ?></p>
        </div>
        <div class="alert alert-success success-find" style="display:none;">
            <p><?php echo __('translation.done_searching', ['count' => '<strong class="counter">N</strong>']); ?></p>
        </div>
        <div class="alert alert-success success-publish" style="display:none;">
            <p><?php echo __('translation.done_publishing_group', ['group' => $group]); ?></p>
        </div>
        <div class="alert alert-success success-publish-all" style="display:none;">
            <p><?php echo __('translation.done_publishing_all'); ?></p>
        </div>
        <?php if(Session::has('successPublish')) : ?>
        <div class="alert alert-info">
            <?php echo Session::get('successPublish'); ?>
        </div>
        <?php endif; ?>
        <p>

            <?php if(!isset($group)) : ?>
            <!--

            <form class="form-import" method="POST" action="<?php echo action('\Barryvdh\TranslationManager\Controller@postImport'); ?>" data-remote="true" role="form">
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
            <div class="form-group">
                <div class="row">
                    <div class="col-sm-3">
                        <select name="replace" class="form-control">
                            <option value="0"><?php echo __('translation.append_new'); ?></option>
                            <option value="1"><?php echo __('translation.replace_existing'); ?></option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                    <button type="submit" class="btn btn-success btn-block"  data-disable-with="<?php echo __('translation.loading'); ?>"><?php echo __('translation.import_groups'); ?></button>
                    </div>
                </div>
            </div>
        </form>
         -->
        <form class="form-find" method="POST" action="<?php echo action('\Barryvdh\TranslationManager\Controller@postFind'); ?>" data-remote="true" role="form"
            data-confirm="<?php echo __('translation.confirm_scan'); ?>">
            <div class="form-group">
                <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                <button type="submit" class="btn btn-info"
                    data-disable-with="<?php echo __('translation.searching'); ?>"><?php echo __('translation.find_translations'); ?></button>
            </div>
        </form>
        <?php endif; ?>
        <?php if(isset($group)) : ?>
        <form class="form-inline form-publish" method="POST" action="<?php echo action('\Barryvdh\TranslationManager\Controller@postPublish', $group); ?>" data-remote="true"
            role="form" data-confirm="<?php echo __('translation.confirm_publish_group', ['group' => $group]); ?>">
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
            <button type="submit" class="btn btn-info"
                data-disable-with="<?php echo __('translation.publishing'); ?>"><?php echo __('translation.publish_translations'); ?></button>
            <a href="<?= action('\Barryvdh\TranslationManager\Controller@getIndex') ?>"
                class="btn btn-default"><?php echo __('translation.back'); ?></a>
        </form>
        <?php endif; ?>
        </p>
        <form role="form" method="POST" action="<?php echo action('\Barryvdh\TranslationManager\Controller@postAddGroup'); ?>">
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
            <div class="form-group">
                <p><?php echo __('translation.choose_group'); ?></p>
                <select name="group" id="group" class="form-control group-select">
                    <?php foreach($groups as $key => $value): ?>
                    <option value="<?php echo $key; ?>"<?php echo $key == $group ? ' selected' : ''; ?>><?php echo $value; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!--
        <div class="form-group">
            <label><?php echo __('translation.enter_new_group'); ?></label>
            <input type="text" class="form-control" name="new-group" />
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-default" name="add-group" value="<?php echo __('translation.add_and_edit_keys'); ?>" />
        </div>
 -->
        </form>
        <?php if($group): ?>
        <!--

        <form action="<?php echo action('\Barryvdh\TranslationManager\Controller@postAdd', [$group]); ?>" method="POST"  role="form">
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
            <div class="form-group">
                <label><?php echo __('translation.add_new_keys'); ?></label>
                <textarea class="form-control" rows="3" name="keys" placeholder="<?php echo __('translation.add_key_placeholder'); ?>"></textarea>
            </div>
            <div class="form-group">
                <input type="submit" value="<?php echo __('translation.add_keys'); ?>" class="btn btn-primary">
            </div>
        </form>
         -->
        <form class="form-add-locale autotranslate-block-group hidden" method="POST" role="form"
            action="<?php echo action('\Barryvdh\TranslationManager\Controller@postTranslateMissing'); ?>">
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="base-locale"><?php echo __('translation.base_locale'); ?></label>
                        <select name="base-locale" id="base-locale" class="form-control">
                            <?php foreach ($locales as $locale): ?>
                            <option value="<?= $locale ?>"><?= $locale ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="new-locale"><?php echo __('translation.enter_target_locale'); ?></label>
                        <input type="text" name="new-locale" class="form-control" id="new-locale"
                            placeholder="<?php echo __('translation.enter_target_locale_placeholder'); ?>" />
                    </div>
                    <?php if(!config('laravel_google_translate.google_translate_api_key')): ?>
                    <p>
                        <code><?php echo __('translation.google_translate_info'); ?></code>
                    </p>
                    <?php endif; ?>
                    <div class="form-group">
                        <input type="hidden" name="with-translations" value="1">
                        <input type="hidden" name="file" value="<?= $group ?>">
                        <button type="submit" class="btn btn-default btn-block"
                            data-disable-with="<?php echo __('translation.adding'); ?>"><?php echo __('translation.auto_translate'); ?></button>
                    </div>
                </div>
            </div>
        </form>

        <hr>

        <!-- <h4><?php echo __('translation.total_changed', ['total' => $numTranslations, 'changed' => $numChanged]); ?></h4> -->
        <table class="table">
            <thead style="position: sticky; top: 0; background: #fff;">
                <tr>
                    <th width="15%"><?php echo __('translation.key'); ?></th>
                    <?php foreach ($locales as $locale): ?>
                    <th><?= $locale ?></th>
                    <?php endforeach; ?>
                    <?php if ($deleteEnabled): ?>
                    <th>&nbsp;</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($translations as $key => $translation): ?>
                <tr id="<?php echo htmlentities($key, ENT_QUOTES, 'UTF-8', false); ?>" class="bg-gray-100 hover:bg-gray-200 transition duration-200">
                    <td class="p-4 border-b"><?php echo htmlentities($key, ENT_QUOTES, 'UTF-8', false); ?></td>
                    <?php foreach ($locales as $locale): ?>
                    <?php $t = isset($translation[$locale]) ? $translation[$locale] : null; ?>

                    <td class="p-4 border-b">
                        <a href="#edit"
                            class="editable status-<?php echo $t ? $t->status : 0; ?> locale-<?php echo $locale; ?> text-blue-600 hover:underline"
                            data-locale="<?php echo $locale; ?>" data-name="<?php echo $locale . '|' . htmlentities($key, ENT_QUOTES, 'UTF-8', false); ?>" id="username"
                            data-type="textarea" data-pk="<?php echo $t ? $t->id : 0; ?>" data-url="<?php echo $editUrl; ?>"
                            data-title="<?php echo __('translation.enter_translation'); ?>"><?php echo $t ? htmlentities($t->value, ENT_QUOTES, 'UTF-8', false) : ''; ?></a>
                    </td>
                    <?php endforeach; ?>
                    <?php if ($deleteEnabled): ?>
                    <td class="p-4 border-b">
                        {{--
                        <a href="<?php echo action('\Barryvdh\TranslationManager\Controller@postDelete', [$group, $key]); ?>" class="delete-key text-red-600 hover:underline"
                            data-confirm="<?php echo __('translation.confirm_delete', ['key' => htmlentities($key, ENT_QUOTES, 'UTF-8', false)]); ?>">
                            <span class="glyphicon glyphicon-trash"></span>
                        </a>
                         --}}
                        <button class="btn btn-xs btn-info auto-translate-key ml-2">
                            <span class="glyphicon glyphicon-globe"></span>
                        </button>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <fieldset>
            <legend><?php echo __('translation.supported_locales'); ?></legend>
            <p>
                <?php echo __('translation.current_supported_locales'); ?>
            </p>
            <form class="form-remove-locale" method="POST" role="form" action="<?php echo action('\Barryvdh\TranslationManager\Controller@postRemoveLocale'); ?>"
                data-confirm="<?php echo __('translation.confirm_remove_locale'); ?>">
                <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                <ul class="list-locales">
                    <?php foreach($locales as $locale): ?>
                    <li>
                        <div class="form-group">

                            <button type="submit" name="remove-locale[<?php echo $locale; ?>]"
                                class="btn btn-danger btn-xs" data-disable-with="...">
                                &times;
                            </button>

                            <pre>
                            <?php print_r($locale); ?>
                            </pre>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </form>

            <form class="form-add-locale" method="POST" role="form" action="<?php echo action('\Barryvdh\TranslationManager\Controller@postAddLocale'); ?>">
                <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                <div class="form-group">
                    <p>
                        <?php echo __('translation.enter_new_locale'); ?>
                    </p>
                    <div class="row">
                        <div class="col-sm-3">
                            <input type="text" name="new-locale" class="form-control" />
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" class="btn btn-default btn-block"
                                data-disable-with="<?php echo __('translation.adding'); ?>"><?php echo __('translation.add_new_locale'); ?></button>
                        </div>
                    </div>
                </div>
            </form>
        </fieldset>
        <fieldset>
            <legend><?php echo __('translation.export_all_translations'); ?></legend>
            <form class="form-inline form-publish-all" method="POST" action="<?php echo action('\Barryvdh\TranslationManager\Controller@postPublish', '*'); ?>"
                data-remote="true" role="form" data-confirm="<?php echo __('translation.confirm_publish_all'); ?>">
                <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                <button type="submit" class="btn btn-primary"
                    data-disable-with="<?php echo __('translation.publishing'); ?>"><?php echo __('translation.publish_all'); ?></button>
            </form>
        </fieldset>

        <?php endif; ?>
    </div>
