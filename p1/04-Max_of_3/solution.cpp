#include <iostream>
using namespace std;

int main()
{
	cout << "This program identifies the largest of three numbers.\n\n";
	
	int a, b, c;
	cout << "Enter integer: "; cin >> a;
	cout << "Enter integer: "; cin >> b;
	cout << "Enter integer: "; cin >> c;
	cout << endl;
	if(a>=b&&a>=c)
		cout << "The largest number is " << a << ".\n";
	else if(b>=a&&b>=c)
		cout << "The largest number is " << b << ".\n";
	else
		cout << "The largest number is " << c << ".\n";
}

