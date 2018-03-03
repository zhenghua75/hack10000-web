for img in `ls *.png`
do
convert -quality 70 ${img} ${img}
done
