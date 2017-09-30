// pages/user/user.js
var app = getApp()
Page( {
  data: {
    userInfo: {},
    userId: 0,
    userType: 1,
    loadingText: '加载中...',
    loadingHidden: false,
  },
  
  onLoad: function () {
      var that = this
      //调用应用实例的方法获取全局数据
      app.getUserInfo(function(userInfo){
        //更新数据
        that.setData({
          userInfo:userInfo,
          userId: app.pubData.userId,
          userType: app.pubData.userType,
          loadingHidden: true
        })
      });
  },
  onShow:function(){

  },

})