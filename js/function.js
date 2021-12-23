function getCaptcha() {
    const tmp = {
        method: "getCaptcha"
    }
    $.post("/api.php", tmp, function (data) {
        $("#captcha").attr("src", data)
    });
}

function isLogin(message, url, code) {
    const tmp = {
        method: "isLogin"
    }
    $.post("/api.php", tmp, function (data) {
        if (data.status === code) {
            alert(message);
            window.location.replace(url);
        }
    });
}

function register() {
    const reUser = /^[a-zA-Z][a-zA-Z0-9_]{4,15}$/;
    const rePass = /^[a-zA-Z]\w{5,17}$/;
    if (!reUser.test($(".form-signup input[name='username']").val())) {
        alert("请输入字母开头5-16位用户名");
        return;
    }
    if (!rePass.test($(".form-signup input[name='password']").val())) {
        alert("请输入字母开头6-18位密码");
        return;
    }
    $.post("/api.php", {
        method: "register",
        user: $(".form-signup input[name='username']").val(),
        passwd: $(".form-signup input[name='password']").val()
    }, function (data) {
        if ($(data).find('error').text() === "0") {
            alert("注册成功");
            window.location.replace("login.html");
        } else if ($(data).find('error').text() === "1") {
            $(".form-signup input[name='username']").val("");
            $(".form-signup input[name='username']").attr("placeholder", "用户名已存在")
        }
    })
}

function login() {
    const reUser = /^[a-zA-Z][a-zA-Z0-9_]{4,15}$/;
    const rePass = /^[a-zA-Z]\w{5,17}$/;
    const reCaptcha = /^\d{5}$/;
    if (!reUser.test($(".form-signin input[name='username']").val())) {
        alert("请输入字母开头5-16位用户名");
        return;
    }
    if (!rePass.test($(".form-signin input[name='password']").val())) {
        alert("请输入字母开头6-18位密码");
        return;
    }
    if (!reCaptcha.test($(".form-signin input[name='captcha']").val())) {
        alert("请输入5位数字验证码");
        return;
    }
    $.post("/api.php", {
        method: "loginUser",
        user: $(".form-signin input[name='username']").val(),
        passwd: $(".form-signin input[name='password']").val(),
        captcha: $(".form-signin input[name='captcha']").val()
    }, function (data) {
        if ($(data).find('error').text() === "0") {
            alert("登录成功");
            window.location.replace("index.html");
        } else if ($(data).find('error').text() === "2") {
            $(".form-signin input[name='username']").val("");
            $(".form-signin input[name='username']").attr("placeholder", "用户名不存在")
        } else if ($(data).find('error').text() === "3") {
            $(".form-signin input[name='password']").val("");
            alert("密码错误");
        } else if ($(data).find('error').text() === "1") {
            $(".form-signin input[name='captcha']").val("");
            $(".form-signin input[name='captcha']").attr("placeholder", "验证码错误")
            getCaptcha();
        }
    })
}

function logout() {
    const tmp = {
        method: "logout"
    }
    $.post("/api.php?method=logout", tmp, function () {
        $.removeCookie('pathID', {path: '/'});
        alert("已注销，正在跳转至登录页面");
        window.location.replace("login.html");
    })
}

function showFile(pathID) {
    let tmp;
    if (pathID == null) {
        tmp = {
            method: "showFile",
            page: $(".pagination .active").children().text(),
            limit: 10
        }
    } else {
        tmp = {
            method: "showFile",
            fid: pathID,
            page: 1,
            limit: 10
        }
    }
    $.post("/api.php", tmp, function (data) {
        if (data.error === "0") {
            $.cookie('pathID', data.FID, {expires: 1});
            $("#folderFid").text(data.FID);
            $("#folderParent").text(data.Parent);
            $("tbody").children().remove();
            if (data.Child != null) {
                for (let file of data.Child) {
                    let fid = "<td  class=\"fid\">" + file.FID + "</td>";
                    let fileMD5 = "<td  class=\"fileMD5\">" + file.FileMD5 + "</td>";
                    let fileSize = "<td class=\"fileSize\">" + bytesToSize(file.FileSize) + "</td>";
                    let control = "<td class=\"fileControl\"><button  type=\"button\" class=\"btn btn-link deleteFile\">删除</button>" +
                        "<button type=\"button\" class=\"btn btn-link renameFile\">重命名</button></td>>";
                    let fileName;
                    if (file.FileMD5 == "") {
                        fileName = "<td class=\"fileName\"><i class=\"bi bi-folder\"></i><button type=\"button\" class=\"btn btn-link openFolder\">"
                            + file.FileName + "</button></td>";
                    } else {
                        fileName = "<td class=\"fileName\"><i class=\"bi bi-file-earmark\"></i><button type=\"button\" class=\"btn btn-link downloadFile\">"
                            + file.FileName + "</button></td>";
                    }
                    let tmp = "<tr>" + fid + fileMD5 + fileName + fileSize + control + "</tr>"
                    $("tbody").append(tmp)
                }
                $("#pageNavigation").children().remove()
                if (data.Page > 1) {
                    $("#pageNavigation").append("<li class=\"page-item pagepre\">" +
                        "<a class=\"page-link\" href=\"#\" tabindex=\"-1\">上一页</a></li>")
                } else {
                    $("#pageNavigation").append("<li class=\"page-item disabled\">" +
                        "<a class=\"page-link\" href=\"#\" tabindex=\"-1\">上一页</a></li>")
                }
                for (let i = 1; i <= data.MaxPage; i++) {
                    $("#pageNavigation").append("<li class=\"page-item pageid\"id=\"page" + i + "\"><a class=\"page-link\" >" + i + "</a></li>")
                }
                $("#page" + data.Page).attr("class", "page-item pageid active")
                if (data.Page != data.MaxPage) {
                    $("#pageNavigation").append("<li class=\"page-item pagenext\">" +
                        "<a class=\"page-link\" href=\"#\" >下一页</a></li>")
                } else {
                    $("#pageNavigation").append("<li class=\"page-item disabled\">" +
                        "<a class=\"page-link\" href=\"#\" >下一页</a></li>")
                }
            }

        }
    })
}

function delFile(fid) {
    let tmp = {method: "delFile", fid: fid.children(".fid").text()}
    $.post("/api.php", tmp, function (data) {
        if (data.error == 0) {
            fid.remove();
        } else {
            alert("删除文件失败")
        }
    })
}

function renameFile(fid) {
    let name = prompt("请输入文件名", "");
    if (name == "") {
        alert("请输入合法文件名");
        return;
    }
    let tmp = {method: "renameFile", newName: name, fid: fid.children(".fid").text()}
    $.post("/api.php", tmp, function (data) {
        if (data.error == 0) {
            fid.children(".fileName").children("button").text(name);
        } else {
            alert("重命名失败");
        }
    })
}

function bytesToSize(bytes) {
    if (bytes == 0) return '';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    let i = Math.floor(Math.log(bytes) / Math.log(k));
    return (bytes / Math.pow(k, i)).toPrecision(4) + ' ' + sizes[i];
}

function newFolder() {
    let name = prompt("请输入文件夹名", "");
    if (name == "") {
        alert("请输入合法文件名");
        return;
    }
    let tmp = {method: "newFolder", folderName: name, Parent: $("#folderFid").text()}
    $.post("/api.php", tmp, function (data) {
        if (data.error == 0) {
            showFile($("#folderFid").text())
        } else {
            alert("创建文件夹失败")
        }
    })
}


function getMD5(file, callBack) {
    let fileReader = new FileReader(),
        blobSlice = File.prototype.mozSlice || File.prototype.webkitSlice || File.prototype.slice,
        chunkSize = 2097152,
        // read in chunks of 2MB
        chunks = Math.ceil(file.size / chunkSize),
        currentChunk = 0,
        spark = new SparkMD5();
    fileReader.onload = function (e) {
        spark.appendBinary(e.target.result); // append binary string
        currentChunk++;
        if (currentChunk < chunks) {
            loadNext();
        } else {
            callBack(spark.end());
        }
    };

    function loadNext() {
        let start = currentChunk * chunkSize,
            end = start + chunkSize >= file.size ? file.size : start + chunkSize;
        fileReader.readAsBinaryString(blobSlice.call(file, start, end));
    };
    loadNext();
};


function uploadFile(fileMD5, file, parent) {
    let tmp = {method: "isExistMD5", fileMD5: fileMD5, parent: parent, fileName: file.name}
    $.post("/api.php", tmp, function (data) {
        if (data.error == 0) {
            showFile($("#folderFid").text())
        } else {
            let data = new FormData();
            data.append('file', file);
            data.append('fileMD5', fileMD5);
            data.append('parent', parent);
            data.append('method', "uploadFile");

            $.ajax({
                url: '/api.php',
                type: 'POST',
                data: data,
                cache: false,
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.error == 0) {
                        showFile($("#folderFid").text())
                    } else {
                        alert("上传失败")
                    }
                }
            });
        }
    })
}
