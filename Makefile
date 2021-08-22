Dockerfile: Dockerfile.in *.docker
	./build

build: Dockerfile
	docker build -rm -t test-include-php .

