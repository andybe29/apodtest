Date.prototype.phpDate = function(fmt) {
	fmt = fmt.replace('y', this.getFullYear() - 2000);
	fmt = fmt.replace('Y', this.getFullYear());
	fmt = fmt.replace('m', (this.getMonth() + 1).padLeft(2, '0'));
	fmt = fmt.replace('d', this.getDate().padLeft(2, '0'));
	fmt = fmt.replace('H', this.getHours().padLeft(2, '0'));
	fmt = fmt.replace('i', this.getMinutes().padLeft(2, '0'));
	fmt = fmt.replace('s', this.getSeconds().padLeft(2, '0'));
	return fmt;
}

Number.prototype.padLeft = function(width, char) {
	if (!char) char = ' ';
	return (('' + this).length >= width) ? ('' + this) : arguments.callee.call(char + this, width, char);
}

Number.prototype.toDate = function() {
	return new Date(1000 * this);
}
