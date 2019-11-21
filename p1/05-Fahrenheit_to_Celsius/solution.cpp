#include <iostream>
#include <iomanip>
using namespace std;

int FahrenheitToCelsius( int fahrenheit )
{
	return (int)((fahrenheit-32)*5.0/9.0);
}

int main()
{	
    int hiF, loF;
    cout << "Enter high and low temperatures (Fahrenheit): ";
    cin >> hiF >> loF;
	
	cout << endl;
	cout << "High (Celsius): " << FahrenheitToCelsius(hiF) << endl;
	cout << "Low (Celsius): " << FahrenheitToCelsius(loF) << endl;
}
