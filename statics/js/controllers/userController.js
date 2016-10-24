'use strict';
/* collect Controllers */
app.controller('userListController', function($scope, $http, $filter, tipDialog) {
    $scope.queryParams = {account:'',name: '', email: '', sex: '', startTime: '', endTime: '',currentPage:1};
    $scope.userCreateForm = {account:'',name: '', password: '', email: '', age: '', sex: '', phone: ''};
    $scope.userDetail = {account:'',name: '', password: '', email: '', age: '', sex: '', phone: ''};
    $scope.userUpdateForm = {account:'',name: '', email: '', age: '', sex: '', phone: ''};
    $scope.createReturnMessage = '';
    $scope.updateReturnMessage = '';
    $scope.sexOptions = [{id: 1, name: '男'}, {id: 2, name: '女'}];
    var tipDialogTimeOut = 2000;

    $scope.load = function() {
        $http.get('/index.php?r=user/search', {params: $scope.queryParams}).success(function(res) {
            if (res.ret == true) {
                var result = res.data;
                $scope.userList = result.data;
                $scope.pager = result.pager;
            } else {
                tipDialog.open({title: '提示信息', template: res.errMsg, isOk: true, timeOut: tipDialogTimeOut});
            }
        });
    }

    $scope.search = function() {
        if ($scope.queryParams.startTime != '') {
            $scope.queryParams.startTime = $filter('date')($scope.queryParams.startTime, 'yyyy-MM-dd');
        }
        if ($scope.queryParams.endTime != '') {
            $scope.queryParams.endTime = $filter('date')($scope.queryParams.endTime, 'yyyy-MM-dd');
        }

        $scope.queryParams.currentPage = 1;
        $http.get('/index.php?r=user/search', {params: $scope.queryParams}).success(function(res) {
            if (res.ret == true) {
                var result = res.data;
                $scope.userList = result.data;
                $scope.pager = result.pager;
            } else {
                tipDialog.open({title: '提示信息', template: res.errMsg, isOk: true, timeOut: tipDialogTimeOut});
            }
        });
    }
    $scope.reset = function() {
        $scope.queryParams = {account:'',name: '', email: '', sex: '', startTime: '', endTime: '',currentPage:1};
    }

    /**
     * 用户详情展示
     * @param {type} name
     * @returns {undefined}
     */
    $scope.showUser = function(id) {
        var params = {
            id: id
        };
        $scope.userDetail = {name: '', password: '', email: '', age: '', sex: '', phone: ''};
        $http.get('/index.php?r=user/detail', {params: params}).success(function(res) {
            if (res.ret == true) {
                $scope.userDetail = res.data;
                easyDialog.open({
                    container: 'detailUserId', //弹窗元素id
                    // autoClose:10000,//自动关闭
                    fixed: false,
                });
            } else {
                tipDialog.open({title: '提示信息', template: res.errMsg, isOk: true, timeOut: tipDialogTimeOut});
            }
        });
    }


    /**
     * 打开用户添加弹窗
     * @returns {undefined}
     */
    $scope.add = function() {
        $scope.userCreateForm = {name: '', password: '', email: '', age: '', sex: '', phone: ''};
        easyDialog.open({
            container: 'createUserId', //弹窗元素id
            // autoClose:10000,//自动关闭
            fixed: false,
        });
    }

    /**
     * 关闭用户添加弹窗
     * @returns {Boolean}
     */
    $scope.closeEasyDialog = function() {
        $scope.userCreateForm = {name: '', password: '', email: '', age: '', sex: '', phone: ''};
        $scope.userDetail = {name: '', password: '', email: '', age: '', sex: '', phone: ''};
        $scope.userUpdateForm = {name: '', password: '', email: '', age: '', sex: '', phone: ''};
        $scope.createReturnMessage = {name: '', password: '', email: '', age: '', sex: '', phone: ''};
        easyDialog.close();
        return true;
    }


    /**
     * 添加用户
     * @returns {Boolean}
     */
    $scope.creatUser = function() {
        var param = {
            user: $scope.userCreateForm
        };
        $http.post('/index.php?r=user/create', param).success(function(res) {
            if (res.ret == 1) {
                $scope.load();
                $scope.closeEasyDialog();
                tipDialog.open({title: '提示信息', template: 'Successfuly', isOk: true, timeOut: tipDialogTimeOut});
            } else {
                if (res.ret == 0) {
                    $scope.createReturnMessage = res.errMsg;
                } else {
                    easyDialog.close();
                    tipDialog.open({title: '提示信息', template: res.errMsg, isOk: true, timeOut: tipDialogTimeOut});
                }
            }
        });
    }

    /**
     * 弹出用户更新窗口
     * @param {type} name
     * @returns {undefined}
     */
    $scope.update = function(id) {
        var params = {
            id: id
        };
        $scope.userUpdateForm = {name: '', password: '', email: '', age: '', sex: '', phone: ''};
        $http.get('/index.php?r=user/detail', {params: params}).success(function(res) {
            if (res.ret == true) {
                $scope.userUpdateForm = res.data;
                easyDialog.open({
                    container: 'updateUserId', //弹窗元素id
                    // autoClose:10000,//自动关闭
                    fixed: false,
                });
            } else {
                tipDialog.open({title: '提示信息', template: res.errMsg, isOk: true, timeOut: tipDialogTimeOut});
            }
        });
    }

    /**
     * 更新用户
     * @returns {Boolean}
     */
    $scope.updateUser = function() {
        var param = {
            user: $scope.userUpdateForm
        };
        $http.post('/index.php?r=user/update', param).success(function(res) {
            if (res.ret == 1) {
                $scope.load();
                $scope.closeEasyDialog();
                tipDialog.open({title: '提示信息', template: 'Successfuly', isOk: true, timeOut: tipDialogTimeOut});
            } else {
                if (res.ret === 0) {
                    $scope.updateReturnMessage = res.errMsg;
                } else {
                    easyDialog.close();
                    tipDialog.open({title: '提示信息', template: res.errMsg, isOk: true, timeOut: tipDialogTimeOut});
                }
            }
        });
    }

    /**
     * 删除用户
     * @returns {undefined}
     */
    $scope.deleteUser = function(id) {
        var yseFn = function(){
            $scope.deleteUserDo(id);
        }
        easyDialog.open({
            container: {
                header: 'Notice',
                content: '<div class="confirmClass">确认删除？</div>',
                yesFn: yseFn,
                noFn: true,
                yesText: 'Confirm',
                noText: 'Cancel'
            }
        });
    }
     /**
     * 删除用户
     * @returns {undefined}
     */
    $scope.deleteUserDo = function(id) {
        $http.get('/index.php?r=user/delete', {params: {id: id}}).success(function(res) {
            if (res.ret == true) {
                tipDialog.open({title: '提示信息', template: 'Successfuly', isOk: true, timeOut: tipDialogTimeOut});
                $scope.load();
            } else {
                tipDialog.open({title: '提示信息', template: res.errMsg, isOk: true, timeOut: tipDialogTimeOut});
            }
        });
    }

    /**
     * 清楚缓存
     * @returns {undefined}
     */
    $scope.clearRedis = function() {
        if (confirm("确定删除吗")) {
            $http.get('/index.php?r=user/clear-redis').success(function(res) {
                if (res.ret == true) {
                    tipDialog.open({title: '提示信息', template: 'Successfuly', isOk: true, timeOut: tipDialogTimeOut});
                    $scope.load();
                } else {
                    tipDialog.open({title: '提示信息', template: res.errMsg, isOk: true, timeOut: tipDialogTimeOut});
                }
            });
        } else {
            return false;
        }
    }
})

