<!DOCTYPE html>

<html>

<head>

    <title>{{ $appSettings['site_title'] ?? config('app.name') }}</title>

</head>

<body>
    <table bgcolor="#414a51" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tbody><tr>
      <td height="50"></td>
    </tr>
    <tr>
      <td align="center" style="text-align:center;vertical-align:top;font-size:0;">
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tbody><tr>
            <td align="center" width="600">
              <!--header-->
              <table class="table-inner" width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
                <tbody><tr>
                  <td bgcolor="{{$appSettings['site_base_color']}}" style="border-top-left-radius:6px; border-top-right-radius:6px;text-align:center;vertical-align:top;font-size:0;" align="center">
                    <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                      <tbody><tr>
                        <td height="20"></td>
                      </tr>
                      <tr>
                        <td align="center" style="font-family: 'Open sans', Arial, sans-serif; color:#FFFFFF; font-size:16px; font-weight: bold;">Contact Us Email</td>
                      </tr>
                      <tr>
                        <td height="20"></td>
                      </tr>
                    </tbody></table>
                  </td>
                </tr>
              </tbody></table>
              <!--end header-->
              <table class="table-inner" width="95%" border="0" cellspacing="0" cellpadding="0">
                <tbody><tr>
                  <td bgcolor="#FFFFFF" align="center" style="text-align:center;vertical-align:top;font-size:0;">
                    <table align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
                      <tbody><tr>
                        <td height="35"></td>
                      </tr>
                      <!--logo-->
                      <tr>
                        <td align="center" style="vertical-align:top;font-size:0;">
                          <a href="#">
                            <img style="display:block; line-height:0px; font-size:0px; border:0px;" src="{{ asset('storage/'.$appSettings['logo']) }}" width="220" alt="img">
                          </a>
                        </td>
                      </tr>
                      <!--end logo-->
                      <tr>
                        <td height="40"></td>
                      </tr>
                      <!--headline-->
                      <tr>
                        <td align="center" style="font-family: 'Open Sans', Arial, sans-serif; font-size: 22px;color:#414a51;font-weight: bold;">From {{$mailData['name']}} ({{$mailData['email']}})</td>
                      </tr>
                      <!--end headline-->
                      <tr>
                        <td align="center" style="text-align:center;vertical-align:top;font-size:0;">
                          <table width="40" border="0" align="center" cellpadding="0" cellspacing="0">
                            <tbody><tr>
                              <td height="20" style=" border-bottom:3px solid {{$appSettings['site_base_color']}};"></td>
                            </tr>
                          </tbody></table>
                        </td>
                      </tr>
                      <tr>
                        <td height="20"></td>
                      </tr>
                      <!--content-->
                      <tr>
                        <td align="left" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:16px; line-height: 28px;">
                            {{$mailData['message']}}
                        </td>
                      </tr>
                      <!--end content-->
                      <tr>
                        <td height="40"></td>
                      </tr>
              
                    </tbody></table>
                  </td>
                </tr>
                <tr>
                  <td height="45" align="center" bgcolor="#f4f4f4" style="border-bottom-left-radius:6px;border-bottom-right-radius:6px;">
                    <table align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
                      <tbody><tr>
                        <td height="10"></td>
                      </tr>
                      <!--preference-->
                      <tr>
                        <td class="preference-link" align="center" style="font-family: 'Open sans', Arial, sans-serif; color:#95a5a6; font-size:14px;">
                          © {{ date('Y') }} <a href="{{ url('/') }}">{{ $appSettings['site_title'] ?? config('app.name') }}</a>&nbsp;. All Rights Reserved. 
                        </td>
                      </tr>
                      <!--end preference-->
                      <tr>
                        <td height="10"></td>
                      </tr>
                    </tbody></table>
                  </td>
                </tr>
              </tbody></table>
            </td>
          </tr>
        </tbody></table>
      </td>
    </tr>
    <tr>
      <td height="60"></td>
    </tr>
  </tbody></table>
</body>

</html>