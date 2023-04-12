// 表单初始化
FromInit('form.form-validation-username');
FromInit('form.form-validation-email');
FromInit('form.form-validation-sms');

$(function()
{
    // 登录页面背景切换
    var count = $('.bg-slides-item').length;
    if(count > 1)
    {
        var temp_old = 0;
        var temp_new = 1;
        var fade_time = 1000;
        var interval_time = 6000;
        setInterval(function()
        {
            $('.bg-slides-item').eq(temp_old).fadeOut(fade_time);
            $('.bg-slides-item').eq(temp_new).fadeIn(fade_time);
            temp_old = temp_new;
            temp_new++;
            if(temp_new > count-1)
            {
                temp_new = 0;
            }
        }, interval_time);
    } else if(count == 1)
    {
        // 只有一张图片则直接显示
        $('.bg-slides-item').show();
    }

    // 查看密码
    $('.eye-submit').on('click', function()
    {
        var $obj = $(this).parent().parent().find('input');
        if($obj.attr('type') == 'password')
        {
            $(this).addClass('cr-green');
            $obj.attr('type', 'text');
        } else {
            $(this).removeClass('cr-green');
            $obj.attr('type', 'password');
        }
    });

    // 短信验证码获取
    $('.verify-submit, .verify-submit-win').on('click', function()
    {
        // 表单发送按钮
        var form_tag = $(this).data('form-tag') || null;
        if(form_tag != null)
        {
            $('body').attr('data-form-tag', form_tag);
        }
        
        // 验证账户
        var $this = $(this);
        var $form_tag = $($('body').attr('data-form-tag'));
        var $accounts = $form_tag.find('input[name="accounts"]');
        var $verify = $('#verify-img-value');
        var $verify_img = $('#verify-img');
        var verify = '';
        if($accounts.hasClass('am-field-valid'))
        {
            // 是否需要先校验图片验证码
            if($this.data('verify') == 1)
            {
                // 开启图片验证码窗口
                $('#verify-win').modal({closeViaDimmer:false});
                $verify_img.trigger("click");
                $verify.val('');
                $verify.focus();
                return false;
            }

            // 验证码窗口操作按钮则更新按钮对象
            var is_win = $(this).data('win');
            if(is_win == 1)
            {
                $this = $form_tag.find('.verify-submit');

                // 验证码参数处理
                verify = $verify.val().replace(/\s+/g, '');

                if(verify.length != 4)
                {
                    Prompt($verify.data('validation-message'));
                    $verify.focus();
                    return false;
                }
            }

            // 验证码时间间隔
            var time_count = parseInt($this.data('time'));
            
            // 按钮交互
            $this.button('loading');
            if(is_win == 1)
            {
                $('.verify-submit-win').button('loading');
            }

            // 发送验证码
            $.ajax({
                url: RequestUrlHandle($('.verify-submit').data('url')),
                type: 'POST',
                data: {"accounts":$accounts.val(), "verify":verify, "type":$form_tag.find('input[name="type"]').val()},
                dataType: 'json',
                success: function(result)
                {
                    if(result.code == 0)
                    {
                        var intervalid = setInterval(function()
                        {
                            if(time_count == 0)
                            {
                                $this.button('reset');
                                if(is_win == 1)
                                {
                                    $('.verify-submit-win').button('reset');
                                }
                                $this.text($this.data('text'));
                                $verify.val('');
                                clearInterval(intervalid);
                            } else {
                                var send_msg = $this.data('send-text').replace(/{time}/, time_count--);
                                $this.text(send_msg);
                            }
                        }, 1000);
                        if($('#verify-win').length > 0)
                        {
                            $('#verify-win').modal('close');
                        }
                    } else {
                        $this.button('reset');
                        if(is_win == 1)
                        {
                            $('.verify-submit-win').button('reset');
                            $verify_img.trigger("click");
                        }
                        Prompt(result.msg);
                    }
                },
                error: function(xhr, type)
                {
                    $this.button('reset');
                    if(is_win == 1)
                    {
                        $('.verify-submit-win').button('reset');
                    }
                    Prompt(HtmlToString(xhr.responseText) || (window['lang_error_text'] || '异常错误'), null, 30);
                }
            });         
        } else {
            if($('#verify-win').length > 0)
            {
                $('#verify-win').modal('close');
            }
            $accounts.focus();
        }
    });
});