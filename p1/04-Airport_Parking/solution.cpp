#include <iostream>
using namespace std;

// First 30 minutes FREE
// 31-60: $2
// Each additional 30 minutes: $1
// Up to seven hours: $14
// Thereafter, each hour: $1
// 24-hour maximum: $21

int main()
{
    int time;
	cout << "Enter parking duration (in minutes): ";
    cin >> time;

    int cost = 0;

    if( time <= 30 ) // 0 - .5 hours
        cost = 0;
    else if( time <= 420 ) // .5 - 7 hours
        cost = time / 30 + (time % 30 != 0);
    else // 7 - 24 hours
    {
        cost = 14 + (time-420)/60 + (time % 60 != 0);
        if( cost > 21 )
            cost = 21;
    }

	cout << endl;
    cout << "Parking cost = $" << cost << endl;
}


