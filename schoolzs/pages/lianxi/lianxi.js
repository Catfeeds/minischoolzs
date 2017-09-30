var app = getApp();
Page({
  data: {
    tel: ''
  },

  onLoad: function (options) {
    // 页面初始化 options为页面跳转所带来的参数
    var that = this;
    //ajax请求数据
    wx.request({
      url: app.pubData.hostUrl + '/Api/Web/getconfig',
      method: 'post',
      data: {},
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var config = res.data.config;
        that.setData({
          tel: config.tel
        })
      },
      error: function (e) {
        wx.showToast({
          title: '网络异常！',
          duration: 2000
        });
      }
    })
  },

  calling:function(){
    wx.makePhoneCall({
      phoneNumber: this.data.tel, //此号码为真实电话号码
      success:function(){
        console.log("拨打电话成功！")
      },
      fail:function(){
        console.log("拨打电话失败！")
      }
    })
  }
})