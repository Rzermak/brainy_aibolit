(function (exports) {
    "use strict";
	
    var aiBolit = function(param){
        "use strict";

         if (param) {
           return aiBolit.variable[param] || false;
         }
         return this;
       },
       themeSetting = typeof exports.themeSetting === 'undefined' ? {} : exports.themeSetting,
       $ = exports.jQuery,
       bootstrap = exports.bootstrap;

    exports.aiBolit = aiBolit;

    aiBolit.variable = {
      init: function () {
        // composite
        this.compositeInit = typeof this.compositeInit !== 'undefined' ? this.compositeInit : false;
      },

      set: function (name, value) {
        this[name] = value;
      }
    };

    /**
     * Polyfill
     */
    aiBolit.zombieBrowser = function () {
      // Check if browser supports document.querySelectorAll('body').forEach();
      if (window.NodeList && !NodeList.prototype.forEach) {
        NodeList.prototype.forEach = Array.prototype.forEach;
      }
      // Check if browser supports document.body.closest()
      if (Element.prototype && !Element.prototype.closest) {
        Element.prototype.matches = Element.prototype.matches || Element.prototype.mozMatchesSelector || Element.prototype.msMatchesSelector || Element.prototype.oMatchesSelector || Element.prototype.webkitMatchesSelector;
        Element.prototype.closest = Element.prototype.closest || function closest(selector) {
          if (!this) return null;
          if (this.matches(selector)) return this;
          if (!this.parentElement) return null;
          else return this.parentElement.closest(selector);
        };
      }
      // Check if browser supports array.includes()
      if (!Array.prototype.includes) {
        Object.defineProperty(Array.prototype, 'includes', {
          value: function(searchElement, fromIndex) {
            return this.indexOf(searchElement, fromIndex) > -1 ? true : false;
          }
        });
      }
      // Check if browser supports new CustomEvent()
      if (typeof CustomEvent !== "function") {
        function CustomEvent (event, params) {
          params = params || {bubbles: false, cancelable: false, detail: null};
          var evt = document.createEvent('CustomEvent');
          evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
          return evt;
        }

        window.CustomEvent = CustomEvent;
      }
      // Check if browser supports :scope natively
      // Thanks for https://stackoverflow.com/a/17989803
      try { 
        document.querySelector(':scope body');
      } catch (err) {
        ['querySelector', 'querySelectorAll'].forEach(function(method) {
          var nativ = Element.prototype[method];
          Element.prototype[method] = function(selectors) {
            if (/(^|,)\s*:scope/.test(selectors)) {
              var id = this.id;
              this.id = 'ID_' + Date.now();
              selectors = selectors.replace(/((^|,)\s*):scope/g, '$1#' + this.id);
              var result = document[method](selectors);
              this.id = id;
              return result;
            } else {
              return nativ.call(this, selectors);
            }
          }
        });
      }
    };

    /**
     * Создать и вызвать кастомное событие
     * @param string name - event name
     * @param mixed params - массив или объект с параметрами
     * @param HTML Node el - объект на который нужно добавить событие (по умолчанию 'document')
     */

    aiBolit.dispatchEvent = function (name, params, el) {
      var event = new CustomEvent(name, {cancelable: true});
      if (params) {
        for (var i in params) {
          event[i] = params[i];
        }
      }
      (el || document).dispatchEvent(event);
    },

    /**
     * Выполнение функции после загрузки страницы
     * @param function func
     */
    aiBolit.ready = function(func) {
      let scope = this,
        args = arguments;
      if (document.readyState != 'loading'){
        func.apply(scope, Array.prototype.slice.call(args));
      } else {
        document.addEventListener('DOMContentLoaded', function () {
          func.apply(scope, Array.prototype.slice.call(args));
        });
      }
    }

    // Получить уникальный идентификатор
    aiBolit.getRandId = function (prefix) {
      let randId = prefix + Math.floor(1 + Math.random() * 1000);
      return document.getElementById(randId) !== null ? aiBolit.getRandId(prefix) : randId;
    };

    /**
     * Получить аттрибут элемента
     * @param HTML Node el - объект, у которого проверяем аттрибут
     * @param string|boolean defaultValue - значение по умолчанию
     * @returns string|boolean
     */

    aiBolit.getAttr = function (el, attr, defaultValue) {
      var value = el.getAttribute(attr) !== null ? el.getAttribute(attr) : defaultValue;
      if (value === 'false') {
        return false;
      } else if (value === 'true') {
        return true;
      } else if (parseInt(value) == value) { // '==' important
        return parseInt(value);
      } else {
        return value;
      }
    };
    
    /**
     * Check string to has Json structure
     * @param sting str
     * @returns {Boolean}
     */
    
    aiBolit.isJson = function (str) {
        if (typeof str !== 'string') return false;
        try {
            const result = JSON.parse(str);
            const type = Object.prototype.toString.call(result);
            return type === '[object Object]' || type === '[object Array]';
        } catch (err) {
            return false;
        }
    };

    /**
     * Проверка, используется ли мобильный телефон
     * @return boolean
     */
    aiBolit.isMobile = function () {
      return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    };

      /**
     * Ширина окна контента
     * @returns float
     */
    aiBolit.contentWidth = function () {
      return document.getElementById('content').offsetWidth;
    };

      /**
     * Ширина окна браузера
     * @returns float
     */
    aiBolit.windowWidth = function () {
      return window.innerWidth;
    };

    /**
     * Высота окна браузера
     * @returns float
     */
    aiBolit.windowHeight = function () {
      return window.innerHeight;
    };
    
    /**
     * Check script in string and insert to head
     * @param string str
     */
    
    aiBolit.checkScript = function (element) {
        Array.prototype.slice.call(element.getElementsByTagName('script')).forEach((el) => {
            let script = document.createElement("script"),
                code = el.innerHTML,
                src = el.getAttribute('src');
            
            if (code) {
				script.text = code;
            } else if (src !== null) {
                script.setAttribute('src', src);
            } else {
                return;
            }
            
            element.appendChild(el.parentElement.removeChild(el));
        });
    },
    
    /**
     * Add event to form and send by ajax
     * @param string|object element
     * @param object options
     * @returns boolean
     */
    
    aiBolit.setAjaxForm = function (element, options) {
        let form = typeof element.tagName !== 'undefined' ? [element] : document.querySelectorAll(element);
        if (form.length <= 0) {
            return false;
        }

        let defaults = {
                action: form[0].getAttribute('action'),
                method: form[0].getAttribute('method') !== null ? form[0].getAttribute('method') : 'GET',
                validate: function (form) {
                    return true;
                },
                beforeSend: function(form) {
                    
                },
                success: function(form, responce) {
                    form.innerHTML = responce;
                    aiBolit.checkScript(form);
                },
                error: function(form, data) {
                    alert_float('danger', data);
                }
            },
            settings = $.extend({}, defaults, options);

        form.forEach(function (formEl) {
            formEl.addEventListener('submit', function (e) {
                e.preventDefault();

                let allow = settings.validate(formEl);
                if (allow !== true) {
                    formEl.querySelectorAll("button[type='submit']").forEach(function (submitEl) {
                        if (submitEl.getAttribute('data-original-text') !== null) {
                            setTimeout(() => {
                                submitEl.classList.remove('disabled');
                                submitEl.removeAttribute('disabled');
                                submitEl.innerText = submitEl.getAttribute('data-original-text');
                            }, 500);
                        }
                    });

                    return false;
                }

                var formArray = [],
                    formData = new FormData(),
                    formDataArray = Array.from(new FormData(formEl));

                for (var i in formDataArray) {
                    formData.append(formDataArray[i][0], formDataArray[i][1]);
                    formArray.push((formDataArray[i][0] + '=' + formDataArray[i][1]));
                }

                formEl.querySelectorAll("input[type='checkbox']").forEach(function (element) {
                    if (element.checked === true) {
                        return;
                    }
                    formData.append(element.getAttribute('name'), 0);
                });
                
                formEl.querySelectorAll("input[type='file']").forEach(function (element) {
                    if (typeof element.files[0] === 'undefined') {
                        return;
                    }
                    formData.append(element.getAttribute('name'), element.files[0]);
                });
                
                settings.beforeSend(formEl, formData);
                
                let action = settings.action;
                if (settings.method === 'GET') {
                  action += (action.match(/\?/) !== null ? '&' : '?') + formArray.join('&');
                }

                var request = new XMLHttpRequest();
                request.open(settings.method, action, true);
                request.addEventListener("load", function(evt) {
                    if (this.status >= 200 && this.status < 400) {
                        settings.success(formEl, this.response, this);
                    } else {
                        settings.error(formEl, this.response);
                    }
                });

                request.addEventListener("error", function(evt) {
                    settings.error(formEl, evt);
                });

                request.send(formData);
            });
        });

        return true;
    }
    
    aiBolit.setLoader = function(element) {
        element = typeof element.tagName !== 'undefined' ? [element] : document.querySelectorAll(element);
        element.forEach((el) => {
            el.insertAdjacentHTML('beforeend', '<div class="content-loader"><i class="fa fa-spinner fa-spin"></i></div>');
        });
    }
    
    aiBolit.removeLoader = function(element) {
        element = typeof element.tagName !== 'undefined' ? [element] : document.querySelectorAll(element);
        element.forEach((el) => {
            el.querySelectorAll(':scope > .content-loader').forEach((elLoader) => {
                elLoader.parentElement.removeChild(elLoader);
            });
        });
    }
    
    aiBolit.checkResponseError = function (response) {
        response = JSON.parse(response);
        if (typeof response.error !== 'undefined' && response.error !== false) {
            alert(response.error);
        }
    },
	
    /**
     * Send ajax data
     * @param object options
     */
    
    aiBolit.ajax = function(options) {
         let defaults = {
                url: '',
                body: '',
                method: 'GET',
                success: function() {
                    if (typeof settings.findEl !== 'undefined') {
                        let el = document.querySelector(settings.findEl);
                        el.innerHTML = this.response;
                        aiBolit.checkScript(el);
                    }
                },
                error: function() {
                    console.log(this.response);
                }
            },
            settings = $.extend({}, defaults, options);
        
        let request = new XMLHttpRequest(),
            scope = request,
            args = [];
                    
        args.push(request);
        for (let i in arguments) {
           args.push(arguments[i]);
        }
        
        request.open(settings.method, settings.url , true);
        request.addEventListener("load", (evt) => {
            if (request.status >= 200 && request.status < 400) {
                settings.success.apply(scope, Array.prototype.slice.call(args));
            } else {
                settings.error.apply(scope, Array.prototype.slice.call(args));
            }
        });

        request.addEventListener("error", (evt) => {
            settings.error.apply(scope, Array.prototype.slice.call(args));
        });

        request.send(settings.body);
        
        return request;
    }
	
    /**
     * Sitelist
     */
    
    aiBolit.siteList = {
        timer: false,
        request: false,
        isLoaded: false,
        
        init: function () {
            if (aiBolit.siteList.isLoaded !== true) {
                aiBolit.siteList.load();
                aiBolit.siteList.isLoaded = true;
            }
            
            // Btn update site list
            document.querySelectorAll('[data-command="update-siteslist"]').forEach((el) => {
                if (aiBolit.getAttr(el, 'data-aibolit-update-siteslist', false) === true) return;
                el.setAttribute('data-aibolit-update-siteslist', true);
                
                el.addEventListener('click', (e) => {
                    e.preventDefault();
                    aiBolit.siteList.load();
                });
            });
            
            // Scan options
            document.querySelectorAll('[data-aibolit-scan]').forEach((el) => {
                if (aiBolit.getAttr(el, 'data-aibolit-scan-init', false) === true) return;
                el.setAttribute('data-aibolit-scan-init', true);
                el.addEventListener('click', (e) => {
                    e.preventDefault();
                    aiBolit.setLoader(el);
                    aiBolit.siteList.stopTimer();
                    aiBolit.ajax({
                        url: 'index.php?do=aibolit&subdo=scan&command=' + el.getAttribute('data-command') + '&site=' + el.getAttribute('data-site'),
                        success: function() {
                            aiBolit.checkResponseError(this.response);
                            aiBolit.siteList.load();
                            setTimeout(() => {
                                aiBolit.removeLoader(el);
                            }, 1000);
                        }
                    });
                });
            });
        },
        
        stopTimer: function () {
            if (aiBolit.siteList.timer !== false) {
                clearTimeout(aiBolit.siteList.timer);
            }
        },
        
        setTimer: function () {
            aiBolit.siteList.stopTimer();
            aiBolit.siteList.timer = setTimeout(() => {
                if (aiBolit.tabs.isActiveTab('web-server') === true) {
                    aiBolit.siteList.load();
                } else {
                    aiBolit.siteList.setTimer();
                }
            }, 5000);
        },
        
        load: function () {
            let btnUpdateEl = document.querySelector('[data-command="update-siteslist"]');
            if (aiBolit.siteList.request !== false) {
                aiBolit.siteList.request.abort();
            }
            aiBolit.siteList.stopTimer();
            aiBolit.setLoader(btnUpdateEl);
            aiBolit.siteList.request = aiBolit.ajax({
                url: 'index.php?do=aibolit&subdo=show_table_sites',
                success: function() {
                    let response = JSON.parse(this.response);
                    aiBolit.siteList.request = false;
                    aiBolit.removeLoader(btnUpdateEl);
                    if (typeof response.error !== 'undefined') {
                        alert(response.content);
                    } else {
                        document.querySelectorAll('[data-siteslist]').forEach((el) => {
                            el.innerHTML = response.content;
                        });
                    }
                    aiBolit.siteList.setTimer();
                    aiBolit.init();
                },
                error: function () {
                    aiBolit.siteList.request = false;
                    aiBolit.removeLoader(btnUpdateEl);
                    aiBolit.siteList.setTimer();
                }
            });
        }
    };
	
    /**
     * Report list
     */
    
    aiBolit.reportList = {
        siteEl: false,
        contentEl: false,
        request: false,
        
        init: function () {
            // Report content element
            document.querySelectorAll('[data-reportlist]').forEach((el) => {
                if (aiBolit.getAttr(el, 'data-aibolit-reportlist', false) === true) return;
                el.setAttribute('data-aibolit-reportlist', true);
                aiBolit.reportList.contentEl = el;
            });
            // Select report site
            document.querySelectorAll('[data-site-report]').forEach((el) => {
                if (aiBolit.getAttr(el, 'data-aibolit-sitereport', false) === true) return;
                el.setAttribute('data-aibolit-sitereport', true);
                aiBolit.reportList.siteEl = el;
                el.addEventListener('change', (e) => {
                    e.preventDefault();
                    aiBolit.reportList.load();
                });
            });
            // View report site with select tab
            document.querySelectorAll('[data-aibolit-reportview]').forEach((el) => {
                if (aiBolit.getAttr(el, 'data-aibolit-reportview-init', false) === true) return;
                el.setAttribute('data-aibolit-reportview-init', true);
                el.addEventListener('click', (e) => {
                    e.preventDefault();
                    aiBolit.reportList.siteEl.value = el.getAttribute('data-site');
                    aiBolit.reportList.load();
                    aiBolit.tabs.setTab('report');
                });
            });
            // Remove file
            document.querySelectorAll('[data-aibolit-removefile]').forEach((el) => {
                if (aiBolit.getAttr(el, 'data-aibolit-removefile-init', false) === true) return;
                el.setAttribute('data-aibolit-removefile-init', true);
                el.addEventListener('click', (e) => {
                    e.preventDefault();
                    show_modal('Удалить файл ' + el.getAttribute('data-file'), true, (accept) => {
                        if (accept === true) {
                            aiBolit.setLoader(el);
                            aiBolit.ajax({
                                url: 'index.php?do=aibolit&subdo=remove_file&file=' + el.getAttribute('data-file') + '&site=' + el.getAttribute('data-site'),
                                success: function() {
                                    aiBolit.removeLoader(el);
                                    let response = JSON.parse(this.response);
                                    if (typeof response.error !== 'undefined') {
                                        alert(response.content);
                                        return false;
                                    }
                                    
                                    aiBolit.reportList.load(true);
                                }
                            });
                        }
                    });
                });
            });
            // View file with form edit
            document.querySelectorAll('[data-aibolit-viewfile]').forEach((el) => {
                if (aiBolit.getAttr(el, 'data-aibolit-viewfile-init', false) === true) return;
                el.setAttribute('data-aibolit-viewfile-init', true);
                el.addEventListener('click', (e) => {
                    e.preventDefault();
                    aiBolit.reportList.editFile(el.getAttribute('data-file'), el.getAttribute('data-site'));
                });
            });
            // Set edit form to ajax
            document.querySelectorAll('form[name="editFile"]').forEach((el) => {
                if (aiBolit.getAttr(el, 'data-aibolit-editfile-form', false) === true) return;
                el.setAttribute('data-aibolit-editfile-form', true);
                
                let submitBtnEl = el.querySelector('[type="submit"]'),
                    resultEl = el.querySelector('[data-update-result]');
                    
                aiBolit.setAjaxForm(el, {
                    beforeSend: function() {
                        aiBolit.setLoader(submitBtnEl);
                    },
                    success: function(form, responce) {
                        aiBolit.removeLoader(submitBtnEl);
                        resultEl.innerHTML = responce;
                        aiBolit.reportList.load();
                        setTimeout(() => {
                            document.getElementById('modal_close').click();
                        }, 1000);
                    },
                    error: function(form, responce) {
                        aiBolit.removeLoader(submitBtnEl);
                        resultEl.innerHTML = 'Error: ' + responce;
                    },
                });
            });
        },
        
        editFile: function (file, site) {
            show_modal('');
            let modalContent = document.querySelector('.tmpModal #content');
            modalContent.innerHTML = '';
            aiBolit.setLoader(modalContent);
            aiBolit.ajax({
                url: 'index.php?do=aibolit&subdo=get_edit_file&file=' + file + '&site=' + site,
                success: function() {
                    aiBolit.removeLoader(modalContent);
                    let response = JSON.parse(this.response);
                    if (typeof response.error !== 'undefined') {
                        modalContent.innerHTML = response.content;
                    } else {
                        modalContent.innerHTML = response.content;
                        aiBolit.init();
                    }
                    
                }
            });
        },
        
        load: function (hideLoader) {
            let site = aiBolit.reportList.siteEl.value,
                showLoader = !hideLoader ? true : false;
            if (!site || site == '') {
                aiBolit.reportList.contentEl.innerHTML = 'Please select site';
                return;
            }
            if (aiBolit.reportList.request !== false) {
                aiBolit.reportList.request.abort();
            }
            if (showLoader === true) {
                aiBolit.reportList.contentEl.innerHTML = '';
                aiBolit.setLoader(aiBolit.reportList.contentEl);
            }
            aiBolit.reportList.request = aiBolit.ajax({
                url: 'index.php?do=aibolit&subdo=show_table_report&site=' + site,
                success: function() {
                    aiBolit.siteList.request = false;
                    if (showLoader === true) {
                        aiBolit.removeLoader(aiBolit.reportList.contentEl);
                    }
                    let response = JSON.parse(this.response);
                    if (typeof response.error !== 'undefined') {
                        aiBolit.reportList.contentEl.innerHTML = response.content;
                    } else {
                        aiBolit.reportList.contentEl.innerHTML = response.content;
                        aiBolit.init();
                    }
                },
                error: function () {
                    aiBolit.siteList.request = false;
                    aiBolit.removeLoader(aiBolit.reportList.contentEl);
                    aiBolit.reportList.contentEl.innerHTML = 'Error generated list report';
                }
            });
        }
    };
	
	
    /**
     * Logs list
     */
    
    aiBolit.logsList = {
        siteEl: false,
        contentEl: false,
        request: false,
        timer: false,
        
        init: function () {
            // Logs content element
            document.querySelectorAll('[data-logslist]').forEach((el) => {
                if (aiBolit.getAttr(el, 'data-aibolit-logslist', false) === true) return;
                el.setAttribute('data-aibolit-logslist', true);
                aiBolit.logsList.contentEl = el;
            });
            // Select logs site
            document.querySelectorAll('[data-site-logs]').forEach((el) => {
                if (aiBolit.getAttr(el, 'data-aibolit-sitelogs', false) === true) return;
                el.setAttribute('data-aibolit-sitelogs', true);
                aiBolit.logsList.siteEl = el;
                el.addEventListener('change', (e) => {
                    e.preventDefault();
                    aiBolit.logsList.load();
                });
            });
            // View logs site with select tab
            document.querySelectorAll('[data-aibolit-logsview]').forEach((el) => {
                if (aiBolit.getAttr(el, 'data-aibolit-logsview-init', false) === true) return;
                el.setAttribute('data-aibolit-logsview-init', true);
                el.addEventListener('click', (e) => {
                    e.preventDefault();
                    aiBolit.logsList.siteEl.value = el.getAttribute('data-site');
                    aiBolit.logsList.load();
                    aiBolit.tabs.setTab('logs');
                });
            });
            
            aiBolit.logsList.setTimer();
        },
        
        stopTimer: function () {
            if (aiBolit.logsList.timer !== false) {
                clearTimeout(aiBolit.logsList.timer);
            }
        },
        
        setTimer: function () {
            aiBolit.logsList.stopTimer();
            aiBolit.logsList.timer = setTimeout(() => {
                if (aiBolit.tabs.isActiveTab('logs') === true) {
                    aiBolit.logsList.load(true);
                } else {
                    aiBolit.logsList.setTimer();
                }
            }, 500);
        },
        
        getLogScrollTop: function () {
            let el = document.querySelector('[data-aibolit-logs]');
            if (el === null) {
                return 'bottom';
            }
            
            return (el.scrollTop + el.clientHeight) == el.scrollHeight ? 'bottom' : el.scrollTop;
        },
        
        setLogScrollTop: function (scrollTop) {
            let el = document.querySelector('[data-aibolit-logs]');
            if (el !== null) {
                el.scrollTop = scrollTop === 'bottom' ? el.scrollHeight : scrollTop;
            }
        },
        
        load: function (hideLoader) {
            let site = aiBolit.logsList.siteEl.value,
                showLoader = !hideLoader ? true : false;
                
            aiBolit.logsList.stopTimer();
            
            if (!site || site == '') {
                aiBolit.logsList.contentEl.innerHTML = 'Please select site';
                return;
            }
            if (aiBolit.logsList.request !== false) {
                aiBolit.logsList.request.abort();
            }
            if (showLoader === true) {
                aiBolit.logsList.contentEl.innerHTML = '';
                aiBolit.setLoader(aiBolit.logsList.contentEl);
            }
            aiBolit.logsList.request = aiBolit.ajax({
                url: 'index.php?do=aibolit&subdo=show_logs&site=' + site,
                success: function() {
                    let content = this.response,
                        error = false,
                        isJson = aiBolit.isJson(this.response);
                    
                    if (isJson === true) {
                        let response = JSON.parse(this.response);
                        if (typeof response.error !== 'undefined') {
                            error = true;
                        }
                        
                        content = response.content;
                    }
                    
                    if (error === true) {
                        aiBolit.removeLoader(aiBolit.logsList.contentEl);
                        aiBolit.logsList.contentEl.innerHTML = content;
                    } else {
                        if (showLoader === true) {
                            aiBolit.removeLoader(aiBolit.logsList.contentEl);
                            aiBolit.logsList.contentEl.innerHTML = content;
                            aiBolit.logsList.setLogScrollTop('bottom');
                        } else {
                            let scrollTop = aiBolit.logsList.getLogScrollTop();
                            aiBolit.logsList.contentEl.innerHTML = content;
                            aiBolit.logsList.setLogScrollTop(scrollTop);
                        }
                    }
                    
                    aiBolit.siteList.request = false;
                    aiBolit.logsList.setTimer();
                    aiBolit.init();
                },
                error: function () {
                    aiBolit.siteList.request = false;
                    aiBolit.removeLoader(aiBolit.logsList.contentEl);
                    aiBolit.logsList.contentEl.innerHTML = 'Error generated list logs';
                    aiBolit.logsList.setTimer();
                }
            });
        }
    };
	
    /**
     * Config form
     */
    
    aiBolit.configForm = {
        init: function () {
            document.querySelectorAll('form[name="aibolitConfig"]').forEach((el) => {
                if (aiBolit.getAttr(el, 'data-aibolit-configform', false) === true) return;
                el.setAttribute('data-aibolit-configform', true);
                
                let submitBtnEl = el.querySelector('[type="submit"]'),
                    fields = {};
                
                el.querySelectorAll('[data-config]').forEach((fieldBlock) => {
                    let fieldName = fieldBlock.getAttribute('data-config'),
                        field = fieldBlock.querySelector('[name="options[' + fieldName + ']"]');
                    fields[fieldName] = {
                        block : fieldBlock,
                        field : field
                    };
                });
                
                fields.cron_type.field.addEventListener('change', () => {
                    let display = fields.cron_type.field.value === 'off' ? 'none' : 'block';
                    fields.cron_time.block.style.display = display;
                    fields.auto_update_base.block.style.display = display;
                    fields.send_email_detected.block.style.display = display;
                    fields.send_email_detected.field.checked = false;
                    fields.email.block.style.display = 'none';
                });
                
                fields.cron_type.field.dispatchEvent(new Event('change'));
                    
                fields.send_email_detected.field.addEventListener('change', () => {
                    let display = fields.send_email_detected.field.checked !== true ? 'none' : 'block';
                    fields.email.block.style.display = display;
                });
                
                fields.send_email_detected.field.dispatchEvent(new Event('change'));
                    
                aiBolit.setAjaxForm(el, {
                    beforeSend: function() {
                        aiBolit.setLoader(submitBtnEl);
                    },
                    success: function(form, responce) {
                        aiBolit.removeLoader(submitBtnEl);
                        submitBtnEl.innerText = 'Сохранено';
                    },
                    error: function(form, responce) {
                        aiBolit.removeLoader(submitBtnEl);
                        submitBtnEl.innerText = 'Ошибка сохранения';
                    },
                });
            });
        }
    };
    
    /**
     * Tabs
     */
    
    aiBolit.tabs = {
        init: function () {
            document.querySelectorAll('[data-tab-btn]').forEach((el) => {
                if (aiBolit.getAttr(el, 'data-aibolit-tab', false) === true) return;
                el.setAttribute('data-aibolit-tab', true);
                
                el.addEventListener('click', (e) => {
                    e.preventDefault();
                    aiBolit.tabs.setTab(el.getAttribute('data-tab-btn'));
                });
            });
        },
        
        setTab: function (tab) {
            let el = document.querySelector('[data-tab-btn="' + tab + '"]');
            
            if (el === null) {
                return false;
            }
            
            el.parentNode.querySelectorAll('[data-tab-btn]').forEach((btnEl) => {
                btnEl.classList.remove('tabs-menu-active');
                document.querySelector('[data-tab="' + btnEl.getAttribute('data-tab-btn') + '"]').style.display = 'none';
            });

            el.classList.add('tabs-menu-active');
            document.querySelector('[data-tab="' + el.getAttribute('data-tab-btn') + '"]').style.display = 'block';
        },
        
        isActiveTab: function (tab) {
            let el = document.querySelector('[data-tab-btn="' + tab + '"]');
            
            if (el === null) {
                return false;
            }
            
            return el.classList.contains('tabs-menu-active') === true ? true : false;
        }
    };
    
    /**
     * Update base
     */
    
    aiBolit.updateBase = {
        versionEl: null,
        versionTimeEl: null,
        versionHistoryEl: null,
        init: function () {
            aiBolit.updateBase.versionEl = document.querySelector('[data-aibolit-versionbase]');
            aiBolit.updateBase.versionTimeEl = document.querySelector('[data-aibolit-versiontime]');
            aiBolit.updateBase.versionHistoryEl = document.querySelector('[data-aibolit-versions-history]');
            aiBolit.updateBase.autoUpdate();
            aiBolit.updateBase.autoFromAddress();
            aiBolit.updateBase.autoFromFile();
        },
        
        autoUpdate: function () {
            document.querySelectorAll('[data-command="update-base"]').forEach((el) => {
                if (aiBolit.getAttr(el, 'data-aibolit-update-base', false) === true) return;
                el.setAttribute('data-aibolit-update-base', true);
                
                let resultEl = document.querySelector('[data-autoupdate-result]');
                el.addEventListener('click', (e) => {
                    e.preventDefault();
                    aiBolit.setLoader(el);
                    aiBolit.ajax({
                        url: 'index.php?do=aibolit&subdo=update_auto',
                        success: function() {
                            aiBolit.removeLoader(el);
                            aiBolit.updateBase.showResultUpdate(this.response, resultEl);
                        }
                    });
                });
            });
        },
        
        autoFromAddress: function () {
            document.querySelectorAll('form[name="updateFromAddress"]').forEach((el) => {
                if (aiBolit.getAttr(el, 'data-aibolit-update', false) === true) return;
                el.setAttribute('data-aibolit-update', true);
                
                let submitBtnEl = el.querySelector('[type="submit"]'),
                    resultEl = el.querySelector('[data-update-result]');
                    
                aiBolit.setAjaxForm(el, {
                    beforeSend: function() {
                        aiBolit.setLoader(submitBtnEl);
                    },
                    success: function(form, responce) {
                        aiBolit.removeLoader(submitBtnEl);
                        aiBolit.updateBase.showResultUpdate(responce, resultEl);
                    },
                    error: function(form, responce) {
                        aiBolit.removeLoader(submitBtnEl);
                        aiBolit.updateBase.showResultUpdate(responce, resultEl);
                    },
                });
            });
        },
        
        autoFromFile: function () {
            document.querySelectorAll('form[name="updateFromFile"]').forEach((el) => {
                if (aiBolit.getAttr(el, 'data-aibolit-update', false) === true) return;
                el.setAttribute('data-aibolit-update', true);
                
                let submitBtnEl = el.querySelector('[type="submit"]'),
                    resultEl = el.querySelector('[data-update-result]');
                    
                aiBolit.setAjaxForm(el, {
                    beforeSend: function() {
                        aiBolit.setLoader(submitBtnEl);
                    },
                    success: function(form, responce) {
                        aiBolit.removeLoader(submitBtnEl);
                        aiBolit.updateBase.showResultUpdate(responce, resultEl);
                    },
                    error: function(form, responce) {
                        aiBolit.removeLoader(submitBtnEl);
                        aiBolit.updateBase.showResultUpdate(responce, resultEl);
                    },
                });
            });
        },
        
        showResultUpdate: function (responce, resultEl) {
            let result = JSON.parse(responce);
            if (result.success === true) {
                aiBolit.updateBase.versionEl.innerText = result.scanner_version;
                aiBolit.updateBase.versionTimeEl.innerText = result.scanner_version_time;
                aiBolit.updateBase.versionHistoryEl.innerText = result.scanner_versions_history;
            }
            resultEl.innerHTML = result.success === true ? 'Success update' : result.error;
            aiBolit.init();
        }
    };
    
    /**
	 * Check plugins
	 */
    
    aiBolit.checkPlugins = {
        init: function () {
            aiBolit.checkPlugins.test();
        },
        
        test: function () {
            try {
              
            } catch (e) {
                console.log(e);
            }
        }
    };

	aiBolit.init = function (update) {
		// Polyfill
		if (!update) {
			aiBolit.zombieBrowser();
		}
        // A timer is made for a faster response of page loading
		setTimeout(function () {
			//* Sitelist
			aiBolit.siteList.init();
			//* Config form
			aiBolit.configForm.init();
			//* Update base
			aiBolit.updateBase.init();
			//* Report list
			aiBolit.reportList.init();
			//* Logs list
			aiBolit.logsList.init();
			//* Tabs
			aiBolit.tabs.init();
			//* Check plugins
			aiBolit.checkPlugins.init();
		}, (update ? 0 : 100));
	};
	
	aiBolit.update = function() {
		aiBolit.init(true);
	};
	
	aiBolit.ready(function() {
		aiBolit.init();
	});
})(window);