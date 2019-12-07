using System;
using System.IO;

public class AtheneStreamReader: StreamReader
{
    public AtheneStreamReader(Stream stream): base(stream) {}

    public override string ReadLine()
    {
        string line = base.ReadLine();
        System.Console.WriteLine("<span class=input>{0}</span>", line);
        return line;
    }
}
