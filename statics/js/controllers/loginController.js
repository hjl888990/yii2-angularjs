'use strict';
/* collect Controllers */
angular.module('app.controllers.loginController', [])

        //用户登录 controller
        .controller('loginController', function($scope, $rootScope, $http, $state) {
            $scope.user = {account:'',password:'',verifyCode:''};
            $scope.authError = null;

            
            $scope.load = function() {//切换大小写提示
                function isIE() {
                    if (!!window.ActiveXObject || "ActiveXObject" in window) {
                        return true;
                    }
                    else {
                        return false;
                    }
                }
                (function() {
                    var inputPWD = document.getElementById('loginPasswd');
                    var capital = false;
                    var capitalTip = {
                        elem: document.getElementById('capital'),
                        toggle: function(s) {
                            var sy = this.elem.style;
                            var d = sy.display;
                            if (s) {
                                sy.display = s;
                            }
                            else {
                                sy.display = d == 'none' ? '' : 'none';
                            }
                        }
                    }
                    var detectCapsLock = function(event) {
                        if (capital) {
                            return
                        }
                        ;
                        var e = event || window.event;
                        var keyCode = e.keyCode || e.which;
                        var isShift = e.shiftKey || (keyCode == 16) || false;
                        if (((keyCode >= 65 && keyCode <= 90) && !isShift) || ((keyCode >= 97 && keyCode <= 122) && isShift)) {
                            capitalTip.toggle('block');
                            capital = true
                        }
                        else {
                            capitalTip.toggle('none');
                        }
                    }
                    if (!isIE()) {
                        inputPWD.onkeypress = detectCapsLock;
                        inputPWD.onkeyup = function(event) {
                            var e = event || window.event;
                            if (e.keyCode == 20 && capital) {
                                capitalTip.toggle();
                                return false;
                            }
                        }
                    }
                })()
            } 
            //检测用户是否登录
            $http.get('/index.php?r=login/ajax-check-login').success(function(d) {
                if (d.ret == '1') {
                    $rootScope.isLogin = true;
                    $state.go('app.user');
                }
            }).error(function(x) {
                $scope.authError = '';
            });

            //验证码
            var captchUrl = '/index.php?r=login/ajax-veryfy';
            $scope.captchUrl = captchUrl;
            $scope.changeCaptch = function() {
                $scope.captchUrl = captchUrl + '&num=' + Math.random();
            };
            
            //用户登录
            $scope.login = function() {
                $scope.authError = '';
                if($scope.user.account == '' || $scope.user.account == null){
                    $scope.authError = '请输入账号.';
                    return false;
                }
                if($scope.user.password == '' || $scope.user.password == null){
                    $scope.authError = '请输入密码.';
                    return false;
                }
                if($scope.user.verifyCode == '' || $scope.user.verifyCode == null){
                    $scope.authError = '请输入验证码.';
                    return false;
                }
                // Try to login
                $http.post('/index.php?r=login/ajax-login', $scope.user).success(function(d) {
                    if (d.ret == '1') {
                        $rootScope.isLogin = true;
                        $state.go('app.user');
                    } else {
                        // 刷新验证码
                        $scope.captchUrl = captchUrl + '&num=' + Math.random();
                        $scope.authError = d.errMsg;
                    }
                }).error(function(x) {
                    // 刷新验证码
                    $scope.captchUrl = captchUrl + '&num=' + Math.random();
                    $scope.authError = 'Server Error';
                });
            };
			
        })
