using System;
using System.IO;


class HelloWorldRunner
{
    static void Main(string[] args)
    {
        // Console.WriteLine("Start HelloWorldRunner");
        Console.SetIn(new AtheneStreamReader(Console.OpenStandardInput()));
        var program = new HelloWorld.Program();
        
        var runprogram = program.GetType().GetMethod("Main", System.Reflection.BindingFlags.NonPublic | System.Reflection.BindingFlags.Static | System.Reflection.BindingFlags.Public);

        if (runprogram != null)
        {
            runprogram.Invoke(program, new object[] { args } );
        }
        // Console.WriteLine("End HelloWorldRunner");
    }
}
