#include <iostream>
using namespace std;

int Round(float x)
{
	return (int)(x < 0 ? x-.5 : x + .5);
}

int main()
{
	cout << "Enter a number: ";
	float x;
	cin >> x;
	cout << "The rounded number is " << Round(x) << endl;

}

