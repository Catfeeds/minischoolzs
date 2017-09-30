// pages/synopsis/synopsis.js
var app = getApp();
Page({
  data:{
    shopInfo:{}
  },

  onLoad:function(options){
    // 页面初始化 options为页面跳转所带来的参数
    var that = this;
    var shopId = options.shopId;
    var that = this;
    wx.request({
      url: app.pubData.hostUrl + '/Api/Shangchang/shop_details',
      method: 'post',
      data: { shop_id: shopId},
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var status = res.data.status;
        if(status==1){
          var shopInfo = res.data.shop_info;
          //that.initProductData(data);
          that.setData({
            shopInfo: shopInfo
          });
        }else{
          wx.showToast({
            title: res.data.err,
            duration: 2000
          });
        }
      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常！',
          duration: 2000
        });
      },
    })
  },
  onReady:function(){
    // 页面渲染完成
  },
  onShow:function(){
    // 页面显示
  },
  onHide:function(){
    // 页面隐藏
  },
  onUnload:function(){
    // 页面关闭
  }
})