git init
git add .
git commit -m "$(date +'%Y-%m-%d %H:%M:%S')" "$@"
git pull origin main
git branch -m main
git push -f git@github.com:detranpb/sigop-alpha-backend.git main:main