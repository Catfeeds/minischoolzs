var reg = /^((\d{11})|^((\d{7,8})|(\d{4}|\d{3})-(\d{7,8})|(\d{4}|\d{3})-(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1})|(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1}))$)$/;
var app = getApp();
Page({
  data:{
    user : {},
    disabled: false,
    array:["个人","企业",],
    index:1,
    blNumber:'',
    truename:'',
    tel:'',
    audit:0,
    ptype:0
  },
  // 上传图片
  chooseImage: function () {
    var that = this
    wx.chooseImage({
      count: 1,
      sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有  
      sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
      success: function (res) {
        var imageSrc = res.tempFilePaths[0];
        wx.uploadFile({
          url: app.pubData.hostUrl + '/Api/User/uploadbl',
          filePath: imageSrc,
          name: 'img',
          formData: {
            uid: app.pubData.userId
          },
          header: {
            'Content-Type': 'multipart/form-data'
          },
          success: function (res) {
            //console.log('uploadImage success, res is:', res);
            var statusCode = res.statusCode;
            if (statusCode==200){
              wx.showToast({
                title: '上传成功',
                icon: 'success',
                duration: 2000
              })
              that.setData({
                imageSrc
              })
            }
          },
          fail: function ({errMsg}) {
            console.log('uploadImage fail, errMsg is', errMsg)
            wx.showToast({
              title: '上传失败',
              icon: 'success',
              duration: 2000
            })
          }
        })

      },
      fail: function ({errMsg}) {
        console.log('chooseImage fail, err is', errMsg)
        wx.showToast({
          title: '图片选择失败',
          icon: 'success',
          duration: 2000
        })
      }
    })
  },

//类型选择 更改事件
bindPickerChange: function(e) {
  console.log(e.detail.value);
    this.setData({
      index: e.detail.value
    })
  },

//营业执照编号失去焦点事件
numberInputEvent:function(e){
    this.setData({
      blNumber:e.detail.value
    })
 },

//窗体加载事件
onLoad: function (options) {
    var that = this;
    var uid = app.pubData.userId;
    wx.request({
      url: app.pubData.hostUrl + '/Api/User/userinfo',
      method: 'post',
      data: {
        uid: uid
      },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var status = res.data.status;
        if (status == 1) {
          var user = res.data.userinfo;
          that.setData({
            blNumber: user.bl_number,
            truename: user.truename,
            tel: user.tel,
            audit: user.audit,
            ptype:user.type,
            user: user
          });
        } else {
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

//提交认证
formDataCommit: function (e) {
    var that = this;
    var userType = that.data.index;
    var truename = that.data.truename;
    var tel = that.data.tel;
    var bl_number = that.data.blNumber;
    if (userType == 1 && !bl_number){
        wx.showToast({
          title: '请输入营业执照编号！',
          duration: 2500
        });
        return false;
    }
    
    wx.request({
      url: app.pubData.hostUrl + '/Api/User/user_edit',
      method: 'post',
      data: { 
        uid:app.pubData.userId,
        usertype: userType,
        truename: truename,
        tel:tel,
        bl_number: bl_number
      },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var status = res.data.status;
        if (status == 1) {
          that.setData({
            disabled: true
          });
          if (userType==1){
            wx.showToast({
              title: '提交成功，请耐心等待审核！',
              duration: 2000
            });
          }else{
            wx.showToast({
              title: '保存成功！',
              duration: 2000
            });
          }
        } else {
          wx.showToast({
            title: res.data.err,
            duration: 2000
          });
        }
        //endInitData
      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常！',
          duration: 2000
        });
      },
    })
  },

//姓名焦点事件
bindKeyname(e) {
  console.log(e.detail.value);
  this.setData({
    truename: e.detail.value,
  })
},

//手机焦点事件
bindTelInput (e){
  console.log(e.detail.value);
    this.setData({
      tel: e.detail.value,
      userver : reg.test(e.detail.value)
    }) 
},

  watch (){
    console.log(1)
  }
})