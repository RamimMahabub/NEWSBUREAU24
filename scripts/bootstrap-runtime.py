import urllib.request,json,zipfile,hashlib,pathlib
root=pathlib.Path('.tools'); data=json.load(urllib.request.urlopen('https://windows.php.net/downloads/releases/releases.json'))
build=data['8.4']['nts-vs17-x64']['zip']; target=root/'php.zip'
print('Downloading',build['path'],flush=True)
urllib.request.urlretrieve('https://windows.php.net/downloads/releases/'+build['path'],target)
assert hashlib.sha256(target.read_bytes()).hexdigest()==build['sha256']
zipfile.ZipFile(target).extractall(root/'php')
(root/'php'/'php.ini').write_text('extension_dir="ext"\nextension=curl\nextension=fileinfo\nextension=mbstring\nextension=openssl\nextension=pdo_mysql\nextension=pdo_sqlite\nextension=sqlite3\nextension=zip\nextension=intl\nextension=gd\nmemory_limit=512M\nupload_max_filesize=12M\npost_max_size=16M\ndate.timezone=Asia/Dhaka\n')
print('Runtime ready',flush=True)
temp=root.resolve()/'tmp'; temp.mkdir(exist_ok=True)
with (root/'php'/'php.ini').open('a') as ini:
    ini.write('upload_tmp_dir="'+temp.as_posix()+'"\nsys_temp_dir="'+temp.as_posix()+'"\noutput_buffering=4096\n')
