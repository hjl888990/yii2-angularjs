'use strict';
/* collect Controllers */
angular.module('app.controllers.headerController', [])
        //注销用户登录 controller
        .controller('headerController', function($scope, $http, $state) {
            //退出登录
            $scope.logout = function() {
                $http.get('/index.php?r=login/ajax-logout').success(function(d) {
                    if (d.ret == '1') {
                            $state.go('access.signin');
                        }
                })
            }


        })
        
        //修改用户密码 controller
        .controller('changeUserMsgController', function($scope, $http,$timeout, $rootScope,tipDialog) {

            var vm = $scope.vm = {
                oldPssword:'',
                newPssword:'',
                newPssword2:''
            };
            //中英文切换
            if ($scope.selectLang == "English") {
                var Notice = 'Notice';
                var almsg = 'Successfully';
                var msg1 = 'Password cannot be empty, please input again!';
                var msg2 = 'Password length cannot be less than 8, please input again!';
                var msg3 = 'Two input password is not the same, please input again!';
                var msg4 = 'The original password is not correct!';
                var msg5='Password must contain uppercase and lowercase letters, Numbers, special characters ';
            } else {
                var Notice = '提示信息';
                var almsg = '操作成功';
                var msg1 = '密码不能为空，请重新输入！';
                var msg2 = '密码长度不能小于8，请重新输入！';
                var msg3 = '两次密码输入不一致，请重新输入！';
                var msg4 = '原密码不正确！';
                var msg5='密码必须包含大写字母，小写字母，数字，特殊字符';
            }

            //提示信息
            var alerts = [{
                    type: 'success',
                    msg: almsg
                }];

            $scope.change = function() {
                
               if(vm.oldPssword=='' || vm.newPssword=='' ||vm.newPssword2==''){
                   tipDialog.open({title: Notice, template: msg1, isOk: true});
                   return false;
               }
               if( vm.newPssword.length<8 || vm.newPssword2.length<8){
                   tipDialog.open({title: Notice, template: msg2, isOk: true});
                   return false;
               }
                var pattern = /^(?=.*?[a-z])(?=.*?[A-Z])(?=.*?\d)(?=.*?[#@*$!$&^~_.]).*$/;
                var password = vm.newPssword;
                if (!password.match(pattern))
                {
                    tipDialog.open({title: Notice, template: msg5, isOk: true});
                    return false;
                }  
               if(vm.newPssword != vm.newPssword2){
                   tipDialog.open({title: Notice, template: msg3, isOk: true});
                   return false;
               }
               
               var params={oldPssword:vm.oldPssword,newPssword:vm.newPssword};                
                $http.post('/index.php?r=site/updateUserMsg',{params: params}).success(function(res) {
                    if (res.ret == true) {
                        vm.oldPssword='';
                        vm.newPssword='';
                        vm.newPssword2='';
                        $scope.alerts = alerts;
                        $timeout(function() {
                            $scope.alerts = [];
                        }, 2000);
                    } else {
                        if(res.ret == '100'){
                            console.log(res);
                           tipDialog.open({title: Notice, template: msg4, isOk: true})
                        }else{
                            console.log(res);
                            tipDialog.open({title: Notice, template:msg5, isOk: true});
                        }
                        
                    }
                });

            }


        })





