from PIL import Image
from pathlib import Path
out=Path('public/images'); out.mkdir(parents=True,exist_ok=True)
image=Image.open('NewsBureau24 Investigative News Homepage.png')
print(image.size)
regions={'lead':(25,237,638,419),'river':(653,237,785,329),'night':(653,342,785,435),'court':(653,446,785,539),'portraits':(653,558,785,650),'investigation':(25,752,400,951),'youth':(417,752,509,826),'cyber':(417,840,509,914),'document':(417,1024,509,1100),'video':(25,1168,380,1283),'interview':(394,1168,505,1237),'raid':(394,1245,505,1313),'narayanganj':(25,1369,127,1472),'dhaka':(133,1369,235,1472),'chattogram':(241,1369,343,1472),'cumilla':(349,1369,454,1472),'map':(486,1369,636,1509)}
for name,box in regions.items(): image.crop(box).save(out/(name+'.webp'),quality=88)
logo=Image.open('NewsBureau24 Investigative News Masthead.png'); logo.save(out/'masthead.webp',quality=92)
