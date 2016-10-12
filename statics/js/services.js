'use strict';
/* Services */
// Demonstrate how to register services
angular.module('app.services', [])

//内部帐号信息 server
        .factory('EmployeeServer', function($http) {
            var data = {};
            return {
                getData: function() {
                    return data;
                }
            };
        })  
        
        

//注销登录 server
        .factory('LogoutServer', function($http, $state) {
            return {
                logout: function() {
                    $http.get('/index.php?r=site/ajaxLoginout').success(function(data) {
                        if (data.ret == '1') {
                            $state.go('access.signin');
                        }
                    })
                }
            };
        })

//注销登录 server
        .factory('ChangePassWord', function($http, $state) {
            return {
                check: function() {
                    $http.get('/index.php?r=site/ajaxCheckoutRedis').success(function(d) {
                        if (d.ret == '-1') {
                            $state.go('app.knowledge.changeUserMsgKnowledge');
                        }
                    });
                }
            };
        })
        
        //关键字标红
        .factory('SearchHighlight', function() {
            return {
                getHtml: function(htmlValue, keyword) {
                    if ("" == keyword)
                        return htmlValue;
                    var temp = htmlValue;
                    var htmlReg = new RegExp("\<.*?\>", "i");
                    var arrA = new Array();
                    //替换HTML标签 
                    for (var i = 0; true; i++)
                    {
                        var m = htmlReg.exec(temp);
                        if (m)
                        {
                            arrA[i] = m;
                        }
                        else
                        {
                            break;
                        }
                        temp = temp.replace(m, "{[(" + i + ")]}");
                    }
                    var words = unescape(keyword.replace(/\+/g, ' ')).split(/\s+/);
                    //替换关键字 
                    for (var w = 0; w < words.length; w++)
                    {
                        var r = new RegExp("(" + words[w].replace(/[(){}.+*?^$|\\\[\]]/g, "\\$&") + ")", "ig");
                        temp = temp.replace(r, "<b style='color:Red;'>$1</b>");
                    }
                    //恢复HTML标签 
                    for (var i = 0; i < arrA.length; i++)
                    {
                        temp = temp.replace("{[(" + i + ")]}", arrA[i]);
                    }
                    return temp;
                }
            };
        })       


// 拦截器
        .factory('myInterceptor', function($q, $translate) {
            var proposedLanguage = $translate.proposedLanguage();
            var interceptor = {
                'request': function(config) {
                    // 所有请求添加一个参数angularjs=true
                    if (config.method.toLowerCase() == 'get') {
                        if (config.url.indexOf('?') != -1) {
                            config.url = config.url + '&currentLanguage=' + proposedLanguage + '&angularjs=true';
                        } else {
                            // config.url = config.url + '?angularjs=true';
                        }
                    } else if (config.method.toLowerCase() == 'post') {
                        //config.data['angularjs'] = 'true';
                        //$rootScope.isPosting = true;
                        config.data['currentLanguage'] = proposedLanguage;
                        config.headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
                        config.data = $.param(config.data);
                    }
                    return config;
                },
                'response': function(response) {

                    // 未登录
                    if (response.data.ret == 401) {
                        window.location.href = response.data.data;
                        return $q.reject('');
                    }
                    if (response.data.ret == 405) {
                        // $state.go('access.signin');
                        window.location.href = response.data.data.signinUrl;
                        return $q.reject('');
                    }
                    // 响应成功
                    return response;
                },
                'requestError': function(rejection) {
                    // 请求发生了错误，如果能从错误中恢复，可以返回一个新的请求或promise
                    return rejection;
                    // 或者，可以通过返回一个rejection来阻止下一步
                    // return $q.reject(rejection);
                },
                'responseError': function(rejection) {
                    // 请求发生了错误，如果能从错误中恢复，可以返回一个新的响应或promise
                    return rejection; // 或新的promise
                    // 或者，可以通过返回一个rejection来阻止下一步
                    // return $q.reject(rejection);
                }
            };
            return interceptor;
        })

// 模态框
        .factory('dialog', function($modal, $rootScope, $q) {
            return {
                open: function(config) {
                    var instance, scope,
                            _template = '';

                    config = config || {};
                    // 判断是否需要标题
                    if (config.title) {
                        _template += '<div class="modal-header"><h4 class="modal-title">' + config.title + '</h4></div>';
                    }
                    if (config.template) {
                        _template += '<div class="modal-body"><div style="text-align: center;">' + config.template + '</div></div>';
                    }
                    // 判断是否需要按钮和底部
                    if (config.isOk || config.isCancel) {
                        _template += '<div class="modal-footer">';
                        if (config.isOk) {
                            _template += '<button class="btn btn-primary" ng-click="ok()">OK</button>';
                        }
                        if (config.isCancel) {
                            _template += '<button class="btn btn-warning" ng-click="cancel()">Cancel</button>';
                        }
                        _template += '</div>';
                    }
                    if (!config.template) {
                        _template = '';
                    }

                    scope = config.scope || $rootScope.$new();
                    angular.extend(scope, {
                        ok: function() {
                            // console.log(instance);
                            if (config.isOk && config.isOk() != false) {
                                instance.close();
                            }
                        },
                        cancel: function() {
                            if (!config.isCancel) {
                                instance.close();
                            } else if (config.isCancel() != false) {
                                instance.close();
                            }
                        }
                    });
                    instance = $modal.open({
                        template: _template,
                        templateUrl: config.templateUrl || '',
                        scope: scope,
                        controller: config.controller,
                        backdrop:true,
                        size: config.size || 'sm',
                        resolve: {}
                    });

                    return instance;
                },
                alert: function(content, callBack) {

                    var instance, temp = '';

                    temp += '<div class="modal-header"><h4 class="modal-title">提示信息</h4></div>';
                    temp += '<div class="modal-body"><div style="text-align: center;">' + content + '</div></div>';
                    temp += '<div class="modal-footer"><button class="btn btn-primary" ng-click="ok()">OK</button></div>';

                    instance = $modal.open({
                        template: temp,
                        controller: function($scope) {
                            $scope.ok = function() {
                                instance.close();
                                callBack && typeof callBack == 'function' && callBack();
                            };
                        },
                        size: 'sm'
                    });

                    // 返回承诺对象
                    return instance;
                },
                confirm: function(content, callBack, cancelCallBack) {
                    var instance, temp = '';
                    temp += '<div class="modal-header"><h4 class="modal-title">提示信息</h4></div>';
                    temp += '<div class="modal-body"><div style="text-align: center;">' + content + '</div></div>';
                    temp += '<div class="modal-footer"><button class="btn btn-primary" ng-click="ok()">OK</button><button class="btn btn-default" ng-click="cancel()">cancel</button></div>';

                    instance = $modal.open({
                        template: temp,
                        controller: function($scope) {
                            $scope.ok = function() {
                                instance.close();
                                callBack && typeof callBack == 'function' && callBack();
                            };
                            $scope.cancel = function() {
                                instance.close();
                                cancelCallBack && typeof cancelCallBack == 'function' && cancelCallBack();
                            };
                        },
                        size: 'sm'
                    });
                }
            };
        })

//提示框服务
        .factory('tipDialog', function($modal,$timeout) {
            return {
                open: function(config) {
                    var instance, _template = '';

                    config = config || {};
                    // 判断是否需要标题
                    if (config.title) {
                        _template += '<div class="modal-header"><h3 class="modal-title">' + config.title + '</h3></div>';

                    }
                    if (config.template) {
                        _template += '<div class="modal-body"><div style="text-align: center;">' + config.template + '</div></div>';
                    }
                    // 判断是否需要按钮和底部
                    if (config.isOk || config.isCancel) {
                        _template += '<div class="modal-footer">';
                        if (config.isOk) {
                            _template += '<button class="btn btn-primary" ng-click="ok()">OK</button>';
                        }
                        if (config.isCancel) {
                            _template += '<button class="btn btn-warning" ng-click="cancel()">Cancel</button>';
                        }
                        _template += '</div>';
                    }
                    var isClose = false;
                    instance = $modal.open({
                        template: _template,
                        templateUrl: config.templateUrl || '',
                        controller: function($scope) {
                            $scope.ok = function() {
                                if (config.isOk != false) {
                                    instance.close();
                                    isClose=true;
                                    if (config.okCallBack && typeof config.okCallBack == 'function') {
                                        config.okCallBack();
                                    }
                                }
                                
                            };
                            $scope.cancel = function() {
                                if (config.isCancel != false) {
                                    instance.close();
                                    isClose=true;
                                }
                            };
                        },
                        windowClass: 'myclass',
                        windowTemplate: '<div class="abc43434"></div>',
                        windowTemplateUrl: '',
                        size: config.size || 'sm',
                        resolve: {}
                    });

                    if (config.timeOut) {
                        $timeout(function() {
                            if(isClose == false){
                                try{
                                    instance.close();
                                }
                                catch(e){
                                    ;
                                }
                                
                            }
                        }, config.timeOut);
                    }

                    return instance;
                },
            };
        })
        
        //提示框服务
        .factory('easyTipDialog', function() {
            return {
                open: function(config) {
                    var btnFn = function() {
                        return true;
                    };
                    easyDialog.open({
                        container: {
                            header: config.title,
                            content: '<div class="modal-body"><div style="text-align: center;min-width: 250px;min-height: 21px;">' + config.template + '</div></div>',
                            yesFn: btnFn,
                            yesText: 'OK',
                        },
                        autoClose : config.timeOut
                    });
                }
            };
        })


//工具JS服务
        .factory('ToolJS', function() {
            var returnFunction = {
                'isEmptyObj': function(obj) {
                    if (typeof obj != "object")
                        return false;
                    for (var x in obj)
                        return false;
                    return true;
                }

            };
            return returnFunction;
        })
