.PHONY: all bower

all: bower

bower:
	@cd static && bower update -p && bower prune -p
