function delRecord(id)
{
    var answer = confirm('Are you sure to delete the record');
    if(answer){
        return true;
    }
    else
    {
        return false;
    }
}
