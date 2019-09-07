var userInfo={
    user:"",
    id:"",
    code:'',
    init:function(){
        this.code=this.GetQueryString('code');
        var user=localStorage.getItem('user');
        if(!user){
            if(this.code){
                this.getUserInfo();
            }else{
                var url=location.href;
                // window.location.href='https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx7ba7568927338678&redirect_uri='+url+'&response_type=code&scope=snsapi_userinfo&state='+this.state+'#wechat_redirect'
                window.location.href='https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx3b61175501c898f8&redirect_uri='+url+'&response_type=code&scope=snsapi_userinfo&state='+this.state+'#wechat_redirect'
            }
        }
    },
    getUserInfo:function(){
        var me=this;
        $.ajax({
            url:"/index.php/extend/wechat/user",
            type:'post',
            data:{code:this.code},
            success:function(res){
                if(res.code==200){
                    me.user=res.data;
                    $(".box img").attr("src",res.data.headimgurl);
                    $(".box .name").text(res.data.nickname);
                    localStorage.setItem('user',JSON.stringify(res.data));
                }else {
                    alert(res.error)
                }
            }
        })
    }, //url参数
    GetQueryString:function(name){
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)","i");
        var r = window.location.search.substr(1).match(reg);
        if (r!=null) return (r[2]); return null;
    }
}