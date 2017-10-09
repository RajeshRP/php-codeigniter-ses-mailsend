# SES one-to-one mail send 

Send mails via SES on codeigniter. Can be useful for someone that doesnot want clone complete source of SES library. This code is written to one-to-one emails. Anyone want to send to multiple has to modify the `sendSes` function to receive receipient array and add the parameters like `Destination.ToAddresses.member1`, `Destination.ToAddresses.member2` etc., accordingly.

## Usage

This class is created as a model. So load the model, & pass the parameters.

```
$this->load->model("ses_model");
$mail = $this->ses_model->sendSes($accesskey,$secretkey,$region,$fromEmail,$senderName,$recipient,$sub,$msg);
```        

`$msg` in above line of code is in HTML format of (UTF-8 character set).